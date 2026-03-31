<?php

namespace Tests\Core\Unit\Controllers;

use Psr\Http\Message\ResponseInterface;
use Tests\Core\Support\ControllerTestCase;
use WScore\Deca\Controllers\AbstractController;
use Psr\Container\ContainerInterface;

class AbstractControllerTest extends ControllerTestCase
{
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

    private function callConcreteController($controller, $request): ResponseInterface
    {
        $response = (new \Nyholm\Psr7\Factory\Psr17Factory())->createResponse();
        return $controller($request, $response, []);
    }
}
