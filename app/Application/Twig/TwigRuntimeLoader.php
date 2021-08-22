<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @license   https://github.com/slimphp/Twig-View/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace App\Application\Twig;

use App\Application\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\App;
use Slim\Interfaces\RouteParserInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class TwigRuntimeLoader implements RuntimeLoaderInterface
{
    /**
     * @var RouteParserInterface
     */
    protected $routeParser;

    /**
     * @var UriInterface
     */
    protected $request;

    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var App
     */
    private $app;

    /**
     * TwigRuntimeLoader constructor.
     *
     * @param App $app
     * @param ServerRequestInterface $request
     * @param SessionInterface $session
     */
    public function __construct(
        App $app,
        ServerRequestInterface $request,
        SessionInterface $session
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $class)
    {
        if (TwigFunctions::class === $class) {
            return new TwigFunctions(
                $this->app,
                $this->request,
                $this->session
            );
        }
        if (TwigFilters::class === $class) {
            return new TwigFilters();
        }

        return null;
    }
}
