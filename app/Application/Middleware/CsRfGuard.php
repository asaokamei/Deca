<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Csrf/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpForbiddenException;

class CsRfGuard implements MiddlewareInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var callable
     */
    private $errorHandler;

    public function __construct(SessionInterface $session, callable $errorHandler = null)
    {
        $this->session = $session;
        $this->errorHandler = $errorHandler;
    }

    private function errorHandler(ServerRequestInterface $request)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new HttpForbiddenException($request, 'CSRF Token invalid');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $token = $request->getParsedBody()[SessionInterface::POST_TOKEN_NAME] ?? '';
            if (!$this->session->validateCsRfToken($token)) {
                if ($this->errorHandler) {
                    return call_user_func($this->errorHandler, $request);
                }
                /** @noinspection PhpUnhandledExceptionInspection */
                $this->errorHandler($request);
            }
        }
        return $handler->handle($request);
    }

    /**
     * @return string
     */
    public function getTokenName(): string
    {
        return $this->tokenName;
    }
}
