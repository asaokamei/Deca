<?php

namespace WScore\Deca\Views\Twig;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Slim\App;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;
use WScore\Deca\Interfaces\MessageInterface;
use WScore\Deca\Interfaces\RoutingInterface;
use WScore\Deca\Interfaces\SessionInterface;
use WScore\Deca\Services\Setting;

class TwigLoader implements TwigLoaderInterface
{
    private RequestInterface $request;

    public function __construct(private ContainerInterface $container)
    {
    }

    public function load(Environment $environment): void
    {
        $environment->addGlobal('_app', $this->container->get(App::class));
        $environment->addGlobal('_setting', $this->container->get(Setting::class));
        $environment->addGlobal('_routes', $this->container->get(RoutingInterface::class));
        if (isset($this->request)) {
            $environment->addGlobal('_request', $this->request);
        }

        $environment->addFunction(new TwigFunction('csrfTokenName', [$this, 'getCsrfTokenName']));
        $environment->addFunction(new TwigFunction('csrfTokenValue', [$this, 'getCsrfTokenValue']));
        $environment->addFunction(new TwigFunction('flashMessages', [$this, 'getFlashMessages']));
        $environment->addFunction(new TwigFunction('flashNotices', [$this, 'getFlashNotices']));
        $environment->addFunction(new TwigFunction('basePath', [$this, 'getBasePath']));
        $environment->addFunction(new TwigFunction('currentUrl', [$this, 'getCurrentUrl']));
        $environment->addFunction(new TwigFunction('url_for', [$this, 'getUrlFor']));
        $environment->addFunction(new TwigFunction('urlFor', [$this, 'getUrlFor']));

        $environment->addFilter(new TwigFilter('arrayToString', [$this, 'filterArrayToString'], ['is_safe' => ['html']]));
        $environment->addFilter(new TwigFilter('mailAddress', [$this, 'filterMailAddressArray'], ['is_safe' => ['html']]));
    }

    public function filterArrayToString(string $path): string
    {
        return json_encode($path);
    }

    public function filterMailAddressArray($address, $name = null): string
    {
        if (is_string($address)) {
            return $this->formMailAddress($address, $name);
        }
        if (is_iterable($address)) {
            $mail = array_key_first($address);
            $name = $address[$mail];
            return $this->formMailAddress($mail, $name);
        }
        return $this->formMailAddress($address, $name);
    }

    private function formMailAddress(string $address, mixed $name): string
    {
        return "{$address} <{$name}>";
    }

    public function getCsrfTokenName(): string
    {
        /** @var SessionInterface $session */
        $session = $this->container->get(SessionInterface::class);
        return $session->getCsRfTokenName();
    }

    public function getCsrfTokenValue(): string
    {
        /** @var SessionInterface $session */
        $session = $this->container->get(SessionInterface::class);
        return $session->getCsRfToken();
    }

    public function getFlashMessages(): array
    {
        /** @var MessageInterface $messages */
        $messages = $this->container->get(MessageInterface::class);
        return $messages->getMessages(MessageInterface::LEVEL_SUCCESS);
    }

    public function getFlashNotices(): array
    {
        /** @var MessageInterface $messages */
        $messages = $this->container->get(MessageInterface::class);
        return $messages->getMessages(MessageInterface::LEVEL_ERROR);
    }

    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getBasePath(): string
    {
        /** @var App $app */
        $app = $this->container->get(App::class);
        return $app->getBasePath();
    }

    public function getCurrentUrl(): string
    {
        if (!isset($this->request)) {
            return '';
        }
        return $this->request->getUri()->getPath();
    }

    public function getUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        /** @var App $app */
        $app = $this->container->get(App::class);
        $routeParser = $app->getRouteCollector()->getRouteParser();
        return $routeParser->urlFor($routeName, $data, $queryParams);
    }
}