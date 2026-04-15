<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace WScore\Deca\Controllers;

use RuntimeException;
use WScore\Deca\Contracts\MessageInterface;
use WScore\Deca\Contracts\RoutingInterface;
use WScore\Deca\Contracts\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use WScore\Deca\Contracts\ValidatorInterface;
use WScore\Deca\Contracts\ValidatorResultInterface;
use WScore\Deca\Contracts\ViewInterface;

abstract class AbstractController
{
    protected ServerRequestInterface $request;

    protected ResponseInterface $response;

    protected array $args = [];

    protected ContainerInterface $container;

    protected ViewInterface $view;
    private ValidatorInterface $validator;
    private ValidatorResultInterface $validatorResult;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $request->getAttribute(ContainerInterface::class);
        $this->args = $args;

        if (method_exists($this, 'action')) {
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            return $this->action($this->args);
        }
        $method = 'on' . $this->determineMethod();
        if (method_exists($this, $method)) {
            return $this->$method($this->args);
        }
        throw new HttpMethodNotAllowedException($request);
    }

    protected function getArgs(): array
    {
        return $this->args;
    }

    protected function getInputs(): array
    {
        return $this->request()->getparsedBody();
    }

    protected function request(): ServerRequestInterface
    {
        return $this->request;
    }

    protected function session(): SessionInterface
    {
        return $this->container->get(SessionInterface::class);
    }

    protected function container(): ContainerInterface
    {
        return $this->container;
    }

    protected function messages(): MessageInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->container->get(MessageInterface::class);
    }

    protected function redirect(): Redirect
    {
        if (isset($this->validatorResult)) {
            if ($this->validatorResult->success()) {
                $this->session()->setFlash('_prev_inputs', $this->validatorResult->getRawDataBag());
            } else {
                $this->session()->setFlash('_prev_inputs', $this->validatorResult->getRawDataBag());
                $this->session()->setFlash('_prev_errors', $this->validatorResult->getErrorBag());
            }
        }
        /** @noinspection PhpUnhandledExceptionInspection */
        return new Redirect($this->container->get(RoutingInterface::class), $this->response, $this->request);
    }

    protected function respond(): Respond
    {
        return new Respond($this->response);
    }

    protected function getView(): ViewInterface
    {
        if (!isset($this->view) || (isset($this->container) && $this->view !== $this->container->get(ViewInterface::class))) {
            $this->view = $this->container->get(ViewInterface::class);
            $this->view->setRequest($this->request);
        }
        $_prev_inputs = $this->session()->getFlash('_prev_inputs');
        $_prev_errors = $this->session()->getFlash('_prev_errors') ?? [];
        if ($_prev_inputs) {
            $this->view->setInputs($_prev_inputs, $_prev_errors);
        }

        if (isset($this->validatorResult)) {
            if ($this->validatorResult->success()) {
                $this->view->setInputs($this->validatorResult->getRawDataBag());
            } else {
                $this->view->setInputs($this->validatorResult->getRawDataBag(), $this->validatorResult->getErrorBag());
            }
        }
        return $this->view;
    }

    protected function view(string $template, array $data = []): ResponseInterface
    {
        $html = $this->drawTemplate($template, $data);
        return $this->respond()->response($html, Respond::OK);
    }

    protected function drawTemplate(string $template, array $data = []): string
    {
        $view = $this->getView();
        /** @noinspection PhpUnhandledExceptionInspection */
        return $view->drawTemplate($template, $data);
    }

    protected function withInputs(array $inputs, array $errors = []): static
    {
        $this->getView()->setInputs($inputs, $errors);
        return $this;
    }

    /**
     * Override this method to change which method to invoke.
     * The default behavior is to use $_POST['_method'], or http method.
     *
     * @return string
     */
    protected function determineMethod(): string
    {
        return $this->request->getParsedBody()['_method'] ?? $this->request->getMethod();
    }

    protected function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    protected function validate(?array $data = null): ValidatorResultInterface
    {
        if (!isset($this->validator)) {
            throw new RuntimeException('validator is not set.');
        }
        $data = $data ?? (
            $this->request->getMethod() === 'GET'
                ? $this->request->getQueryParams()
                : $this->request->getParsedBody()
        );
        return $this->validatorResult = $this->validator->validate($data);
    }
}
