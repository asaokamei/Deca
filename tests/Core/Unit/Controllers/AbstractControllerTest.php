<?php

namespace Tests\Core\Unit\Controllers;

use Psr\Http\Message\ResponseInterface;
use Tests\Core\Support\ControllerTestCase;
use WScore\Deca\Controllers\AbstractController;
use Psr\Container\ContainerInterface;

use WScore\Deca\Contracts\MessageInterface;
use WScore\Deca\Contracts\SessionInterface;
use WScore\Deca\Contracts\ValidatorInterface;
use WScore\Deca\Contracts\ValidatorResultInterface;
use WScore\Deca\Contracts\MessageBagInterface;
use WScore\Deca\Controllers\Redirect;
use WScore\Deca\Controllers\Respond;

class AbstractControllerTest extends ControllerTestCase
{
    public function testGetHelpers(): void
    {
        $request = $this->createRequest('POST', '/test', ['foo' => 'bar']);
        $request = $request->withAttribute(ContainerInterface::class, $this->container);

        $controller = new class extends AbstractController {
            public function onPost(array $args): ResponseInterface
            {
                $test = [
                    'request' => $this->request() === $this->request,
                    'session' => $this->session() instanceof SessionInterface,
                    'container' => $this->container() instanceof ContainerInterface,
                    'messages' => $this->messages() instanceof MessageInterface,
                    'args' => $this->getArgs() === $args,
                    'inputs' => $this->getInputs() === ['foo' => 'bar'],
                ];
                $this->response->getBody()->write(json_encode($test));
                return $this->response;
            }
        };

        $response = $this->callConcreteController($controller, $request, ['id' => '123']);
        $result = json_decode((string)$response->getBody(), true);
        
        $this->assertTrue($result['request'], 'request helper failed');
        $this->assertTrue($result['session'], 'session helper failed');
        $this->assertTrue($result['container'], 'container helper failed');
        $this->assertTrue($result['messages'], 'messages helper failed');
        $this->assertTrue($result['args'], 'args helper failed');
        $this->assertTrue($result['inputs'], 'inputs helper failed');
    }

    public function testViewHelpers(): void
    {
        $request = $this->createRequest('GET', '/test');
        $request = $request->withAttribute(ContainerInterface::class, $this->container);

        $controller = new class extends AbstractController {
            public function onGet(): ResponseInterface
            {
                // test withInputs
                $this->withInputs(['old' => 'val'], ['err' => 'msg']);
                // test drawTemplate (test.twig is defined in ControllerTestCase)
                $html = $this->drawTemplate('test.twig', ['name' => 'Deca']);
                return $this->view('test.twig', ['name' => 'World']);
            }
        };

        $response = $this->callConcreteController($controller, $request);
        $this->assertEquals('Hello World!', (string)$response->getBody());
        
        // Check if inputs were set in the view via Reflection
        $view = $this->container->get(\WScore\Deca\Contracts\ViewInterface::class);
        $refl = new \ReflectionClass($view);
        
        $valueLoaderProp = $refl->getProperty('valueLoader');
        $valueLoader = $valueLoaderProp->getValue($view);
        
        // Manually set request to valueLoader as it's normally done by load() which is called during render
        $vlRefl = new \ReflectionClass($valueLoader);
        $reqProp = $vlRefl->getProperty('request');
        $reqProp->setValue($valueLoader, $request);

        $this->assertEquals('val', $valueLoader->value('old'));
        $this->assertEquals('msg', $valueLoader->error('err', '%s'));
    }

    public function testValidationSuccess(): void
    {
        $request = $this->createRequest('POST', '/test', ['name' => 'John']);
        $request = $request->withAttribute(ContainerInterface::class, $this->container);

        $validator = $this->createMock(ValidatorInterface::class);
        $result = $this->createMock(ValidatorResultInterface::class);
        $dataBag = $this->createMock(MessageBagInterface::class);
        $dataBag->method('getByName')->with('name')->willReturn('John');
        
        $result->method('success')->willReturn(true);
        $result->method('getRawDataBag')->willReturn($dataBag);
        $validator->method('validate')->willReturn($result);

        $controller = new class($validator) extends AbstractController {
            public function __construct($validator) { $this->setValidator($validator); }
            public function onPost(): ResponseInterface
            {
                $res = $this->validate();
                $this->response->getBody()->write($res->success() ? 'success' : 'failed');
                return $this->response;
            }
        };

        $response = $this->callConcreteController($controller, $request);
        $this->assertEquals('success', (string)$response->getBody());

        // Check if validated data is passed to view via getView()
        $view = $this->container->get(\WScore\Deca\Contracts\ViewInterface::class);
        $refl = new \ReflectionClass($view);

        $valueLoaderProp = $refl->getProperty('valueLoader');
        $valueLoader = $valueLoaderProp->getValue($view);

        $vlRefl = new \ReflectionClass($valueLoader);
        $reqProp = $vlRefl->getProperty('request');
        $reqProp->setValue($valueLoader, $request);

        $this->assertEquals('John', $valueLoader->value('name'));
    }

    public function testValidationFailed(): void
    {
        // Re-create container for specific test view logic
        $this->container = $this->createContainer();
        // In ControllerTestCase, ViewInterface is bound to ViewTwig which uses TwigValueLoader.
        $this->container->set(\WScore\Deca\Contracts\ViewInterface::class, function () {
            $loader = new \Twig\Loader\ArrayLoader([
                'test_error.twig' => 'Error: {{ getError("name") }}',
            ]);
            $twig = new \Twig\Environment($loader);
            return new \WScore\Deca\Views\Twig\ViewTwig($twig);
        });

        $request = $this->createRequest('POST', '/test', ['name' => '']);
        $request = $request->withAttribute(ContainerInterface::class, $this->container);

        $validator = $this->createMock(ValidatorInterface::class);
        $result = $this->createMock(ValidatorResultInterface::class);
        
        $errorBag = new \WScore\Deca\Views\FormDotted(['name' => 'required']);
        $rawDataBag = new \WScore\Deca\Views\FormData(['name' => '']);
        
        $result->method('success')->willReturn(false);
        $result->method('getErrorBag')->willReturn($errorBag);
        $result->method('getRawDataBag')->willReturn($rawDataBag);
        $validator->method('validate')->willReturn($result);

        // Define a unique class name to avoid static variable sharing if possible? 
        // No, static is per-method in PHP, so it's shared across all instances of subclasses if they call the same method.
        
        $controller = new class($validator) extends AbstractController {
            public function __construct($validator) { $this->setValidator($validator); }
            public function onPost(): ResponseInterface
            {
                $this->validate();
                // We call getView directly on THIS instance. 
                // If it's already set in AbstractController::getView, it will return the old one.
                $view = $this->getView();
                $this->response->getBody()->write($view->drawTemplate('test_error.twig', ['name' => 'test']));
                return $this->response;
            }
        };

        $response = $this->callConcreteController($controller, $request);
        $this->assertStringContainsString('Error: required', (string)$response->getBody());
    }

    public function testRedirectAndRespondHelpers(): void
    {
        $request = $this->createRequest('GET', '/test');
        $request = $request->withAttribute(ContainerInterface::class, $this->container);

        $controller = new class extends AbstractController {
            public function onGet(): ResponseInterface
            {
                $test = [
                    'redirect' => $this->redirect() instanceof Redirect,
                    'respond' => $this->respond() instanceof Respond,
                ];
                $this->response->getBody()->write(json_encode($test));
                return $this->response;
            }
        };

        $response = $this->callConcreteController($controller, $request);
        $result = json_decode((string)$response->getBody(), true);
        $this->assertTrue($result['redirect']);
        $this->assertTrue($result['respond']);
    }

    public function testRedirectWithValidationFlash(): void
    {
        $request = $this->createRequest('POST', '/test', ['foo' => 'bar']);
        $request = $request->withAttribute(ContainerInterface::class, $this->container);

        $validator = $this->createMock(ValidatorInterface::class);
        $result = $this->createMock(ValidatorResultInterface::class);
        $dataBag = $this->createMock(MessageBagInterface::class);
        $errorBag = $this->createMock(MessageBagInterface::class);
        
        $result->method('success')->willReturn(false);
        $result->method('getRawDataBag')->willReturn($dataBag);
        $result->method('getErrorBag')->willReturn($errorBag);
        $validator->method('validate')->willReturn($result);

        $controller = new class($validator) extends AbstractController {
            public function __construct($validator) { $this->setValidator($validator); }
            public function onPost(): ResponseInterface
            {
                $this->validate();
                return $this->redirect()->toUrl('/somewhere');
            }
        };

        $this->callConcreteController($controller, $request);
        
        $session = $this->container->get(SessionInterface::class);
        $this->assertSame($dataBag, $session->getFlash('_prev_inputs'));
        $this->assertSame($errorBag, $session->getFlash('_prev_errors'));
    }

    public function testInvokeDispatchesToOnGet(): void
    {
        $request = $this->createRequest('GET', '/test');
        $request = $request->withAttribute(ContainerInterface::class, $this->container);
        
        $controller = new class extends AbstractController {
            public function onGet(): ResponseInterface
            {
                $this->response->getBody()->write('onGet called');
                return $this->response;
            }
        };

        $response = $this->callConcreteController($controller, $request);
        $this->assertEquals('onGet called', (string)$response->getBody());
    }

    public function testInvokeDispatchesToOnPost(): void
    {
        $request = $this->createRequest('POST', '/test');
        $request = $request->withAttribute(ContainerInterface::class, $this->container);

        $controller = new class extends AbstractController {
            public function onPost(): ResponseInterface
            {
                $this->response->getBody()->write('onPost called');
                return $this->response;
            }
        };

        $response = $this->callConcreteController($controller, $request);
        $this->assertEquals('onPost called', (string)$response->getBody());
    }

    public function testInvokeDispatchesToMethodFromParsedBody(): void
    {
        $request = $this->createRequest('POST', '/test', ['_method' => 'PUT']);
        $request = $request->withAttribute(ContainerInterface::class, $this->container);

        $controller = new class extends AbstractController {
            public function onPUT(): ResponseInterface
            {
                $this->response->getBody()->write('onPUT called');
                return $this->response;
            }
        };

        $response = $this->callConcreteController($controller, $request);
        $this->assertEquals('onPUT called', (string)$response->getBody());
    }

    public function testInvokeDispatchesToActionIfItExists(): void
    {
        $request = $this->createRequest('GET', '/test');
        $request = $request->withAttribute(ContainerInterface::class, $this->container);

        $controller = new class extends AbstractController {
            public function action(): ResponseInterface
            {
                $this->response->getBody()->write('action called');
                return $this->response;
            }
        };

        $response = $this->callConcreteController($controller, $request);
        $this->assertEquals('action called', (string)$response->getBody());
    }

    private function callConcreteController($controller, $request, array $args = []): ResponseInterface
    {
        $response = (new \Nyholm\Psr7\Factory\Psr17Factory())->createResponse();
        return $controller($request, $response, $args);
    }
}
