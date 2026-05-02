<?php

namespace WScore\Deca\Views\Twig;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;
use WScore\Deca\Contracts\IdentityInterface;
use WScore\Deca\Contracts\MessageInterface;
use WScore\Deca\Contracts\RoutingInterface;
use WScore\Deca\Contracts\SessionInterface;
use WScore\Deca\Services\Setting;

class TwigLoader implements TwigLoaderInterface
{
    private ?ServerRequestInterface $request = null;

    public function __construct(private ContainerInterface $container)
    {
    }

    public function load(Environment $environment): void
    {
        $environment->addGlobal('_app', $this->container->get(App::class));
        $environment->addGlobal('_setting', $this->container->get(Setting::class));
        $environment->addGlobal('_routes', $this->container->get(RoutingInterface::class));
        if ($this->request !== null) {
            $environment->addGlobal('_request', $this->request);
        }

        // Identity (request attribute {@see IdentityInterface::class})
        $environment->addFunction(new TwigFunction('isUserLoggedIn', [$this, 'isUserLoggedIn']));
        $environment->addFunction(new TwigFunction('getDisplayName', [$this, 'getDisplayName']));
        $environment->addFunction(new TwigFunction('getUserId', [$this, 'getUserId']));
        $environment->addFunction(new TwigFunction('is_granted', [$this, 'isGranted']));

        // CSRF Tokens
        $environment->addFunction(new TwigFunction('csrfTokenTag', [$this, 'getCsrfTokenTag']));
        $environment->addFunction(new TwigFunction('csrfTokenName', [$this, 'getCsrfTokenName']));
        $environment->addFunction(new TwigFunction('csrfTokenValue', [$this, 'getCsrfTokenValue']));

        // Flash Messages
        $environment->addFunction(new TwigFunction('flashMessages', [$this, 'getFlashMessages']));
        $environment->addFunction(new TwigFunction('flashNotices', [$this, 'getFlashNotices']));

        // URL Helpers
        $environment->addFunction(new TwigFunction('basePath', [$this, 'getBasePath']));
        $environment->addFunction(new TwigFunction('asset', [$this, 'getBasePath']));
        $environment->addFunction(new TwigFunction('currentUrl', [$this, 'getCurrentUrl']));

        // route() and path() are aliases for url_for()
        $environment->addFunction(new TwigFunction('url_for', [$this, 'getUrlFor']));
        $environment->addFunction(new TwigFunction('urlFor', [$this, 'getUrlFor']));
        $environment->addFunction(new TwigFunction('route', [$this, 'getUrlFor']));
        $environment->addFunction(new TwigFunction('path', [$this, 'getUrlFor']));

        // Other Filters
        $environment->addFilter(new TwigFilter('arrayToString', [$this, 'filterArrayToString'], ['is_safe' => ['html']]));
        $environment->addFilter(new TwigFilter('mailAddress', [$this, 'filterMailAddressArray'], ['is_safe' => ['html']]));
    }

    public function filterArrayToString(array $data, ?string $format = null): string
    {
        if ($format === 'json') {
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $format = $format ?? '<br>';
        return implode($format, $data);
    }

    public function filterMailAddressArray($address, $name = null): string
    {
        if (is_string($address)) {
            return $this->formMailAddress($address, $name);
        }
        if (is_iterable($address)) {
            $formatted = [];
            foreach ($address as $mail => $name) {
                if (is_numeric($mail)) {
                    $mail = $name;
                    $name = null;
                }
                $formatted[] = $this->formMailAddress($mail, $name);
            }
            return implode(', ', $formatted);
        }
        return (string) $address;
    }

    private function formMailAddress(string $address, mixed $name): string
    {
        if (empty($name)) {
            return $address;
        }
        return "{$name} <{$address}>";
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

    public function getCsrfTokenTag(): string
    {
        return "<input type='hidden' name='{$this->getCsrfTokenName()}' value='{$this->getCsrfTokenValue()}' />";
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

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function isUserLoggedIn(): bool
    {
        return $this->getIdentity() instanceof IdentityInterface;
    }

    public function getDisplayName(): string
    {
        $identity = $this->getIdentity();

        return $identity instanceof IdentityInterface ? $identity->getDisplayName() : '';
    }

    public function getUserId(): string
    {
        $identity = $this->getIdentity();

        return $identity instanceof IdentityInterface ? $identity->getId() : '';
    }

    /**
     * Symfony-style role check: {@see IdentityInterface::getRoles()} must contain {@see $attribute}.
     * If {@see $subject} is non-null, this default implementation returns false (reserved for app-level voters).
     */
    public function isGranted(string $attribute, mixed $subject = null): bool
    {
        if ($subject !== null) {
            return false;
        }
        $identity = $this->getIdentity();
        if (!$identity instanceof IdentityInterface) {
            return false;
        }

        return in_array($attribute, $identity->getRoles(), true);
    }

    private function getIdentity(): ?IdentityInterface
    {
        if ($this->request === null) {
            return null;
        }
        $value = $this->request->getAttribute(IdentityInterface::class);
        if (!$value instanceof IdentityInterface) {
            return null;
        }

        return $value;
    }

    public function getBasePath(): string
    {
        /** @var App $app */
        $app = $this->container->get(App::class);
        return $app->getBasePath();
    }

    public function getCurrentUrl(): string
    {
        if ($this->request === null) {
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