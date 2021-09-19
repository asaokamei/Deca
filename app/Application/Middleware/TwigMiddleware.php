<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @license   https://github.com/slimphp/Twig-View/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Interfaces\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;

class TwigMiddleware implements MiddlewareInterface
{
    /**
     * @var ViewInterface
     */
    protected $twig;

    /**
     * @var string|null
     */
    protected $attributeName;

    /**
     * @param App    $app
     * @param string $containerKey
     *
     * @return TwigMiddleware
     */
    public static function createFromContainer(App $app, string $containerKey = 'view'): self
    {
        $container = $app->getContainer();
        $twig = $container->get($containerKey);

        return new self(
            $twig
        );
    }

    /**
     * @param ViewInterface $twig
     * @param string|null $attributeName
     */
    public function __construct(
        ViewInterface $twig,
        ?string $attributeName = null
    ) {
        $this->twig = $twig;
        $this->attributeName = $attributeName;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        if ($this->attributeName !== null) {
            $request = $request->withAttribute($this->attributeName, $this->twig);
        }

        return $handler->handle($request);
    }
}
