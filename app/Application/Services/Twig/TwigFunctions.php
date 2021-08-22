<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @license   https://github.com/slimphp/Twig-View/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace App\Application\Services\Twig;

use App\Application\Interfaces\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\App;
use Slim\Interfaces\RouteParserInterface;

class TwigFunctions
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var RouteParserInterface
     */
    protected $routeParser;

    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var UriInterface
     */
    protected $uri;

    /**
     * @param App $app
     * @param ServerRequestInterface $request Uri
     * @param SessionInterface $session
     */
    public function __construct(
        App $app,
        ServerRequestInterface $request,
        SessionInterface $session
    ) {
        $this->setRequest($request);
        $this->routeParser = $app->getRouteCollector()->getRouteParser();
        $this->basePath = $app->getBasePath();
        $this->session = $session;
    }

    public function getCsrfTokens(): string
    {
        $name = SessionInterface::POST_TOKEN_NAME;
        $value = $this->session->getCsRfToken();
        return <<< END_TAGS
<input type="hidden" name="{$name}" value="{$value}">
END_TAGS;
    }

    /**
     * @return string[]
     */
    public function getFlashMessages(): array
    {
        return (array) $this->session->getFlash('messages', []);
    }

    /**
     * @return string[]
     */
    public function getFlashNotices(): array
    {
        return (array) $this->session->getFlash('notices', []);
    }

    /**
     * Get the url for a named route
     *
     * @param string                $routeName   Route name
     * @param array<string, string> $data        Route placeholders
     * @param array<string, string> $queryParams Query parameters
     *
     * @return string
     */
    public function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }

    /**
     * Get the full url for a named route
     *
     * @param string                $routeName   Route name
     * @param array<string, string> $data        Route placeholders
     * @param array<string, string> $queryParams Query parameters
     *
     * @return string
     */
    public function fullUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->fullUrlFor($this->uri, $routeName, $data, $queryParams);
    }

    /**
     * @param string                $routeName Route name
     * @param array<string, string> $data      Route placeholders
     *
     * @return bool
     */
    public function isCurrentUrl(string $routeName, array $data = []): bool
    {
        $currentUrl = $this->basePath.$this->uri->getPath();
        $result = $this->routeParser->urlFor($routeName, $data);

        return $result === $currentUrl;
    }

    /**
     * Get current path on given Uri
     *
     * @param bool $withQueryString
     *
     * @return string
     */
    public function getCurrentUrl(bool $withQueryString = false): string
    {
        $currentUrl = $this->basePath.$this->uri->getPath();
        $query = $this->uri->getQuery();

        if ($withQueryString && !empty($query)) {
            $currentUrl .= '?'.$query;
        }

        return $currentUrl;
    }

    /**
     * Get the uri
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function setRequest(ServerRequestInterface $request): self
    {
        $this->request = $request;
        $this->uri = $request->getUri();

        return $this;
    }

    /**
     * Get the base path
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Set the base path
     *
     * @param string $basePath
     *
     * @return self
     */
    public function setBasePath(string $basePath): self
    {
        $this->basePath = $basePath;

        return $this;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->request->getAttribute($key, $default);
    }
}
