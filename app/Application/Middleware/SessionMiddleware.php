<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Session\SessionInterface;
use Aura\Session\SessionFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements Middleware
{
    const SESSION_NAME = 'session';

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $request = $request->withAttribute(self::SESSION_NAME, $this->session);

        return $handler->handle($request);
    }
}
