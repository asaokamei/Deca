<?php
declare(strict_types=1);

namespace App\Routes\Utils;

use App\Application\Interfaces\MessageInterface;
use App\Application\Interfaces\RoutingInterface;
use App\Application\Interfaces\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractResponder
{
    use InvokeMethodTrait;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return $this
     */
    public function set(ServerRequestInterface $request, ResponseInterface $response): AbstractResponder
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $request->getAttribute(ContainerInterface::class);

        return $this;
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    protected function getSession(): SessionInterface
    {
        return $this->getContainer()->get(SessionInterface::class);
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function getMessages(): MessageInterface
    {
        return $this->getContainer()->get(MessageInterface::class);
    }

    protected function redirect(): Redirect
    {
        return new Redirect($this->container->get(RoutingInterface::class), $this->response);
    }

    protected function respond(): Respond
    {
        return new Respond($this->container, $this->response);
    }

    protected function regenerateCsRfToken()
    {
        $this->getSession()->regenerateCsRfToken();
    }
}
