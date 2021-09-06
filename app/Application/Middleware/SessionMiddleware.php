<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Interfaces\MessageInterface;
use App\Application\Interfaces\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements Middleware
{
    const SESSION_NAME = 'session';
    const MESSAGE_NAME = 'message';

    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var MessageInterface
     */
    private $message;

    public function __construct(SessionInterface $session, MessageInterface $message)
    {
        $this->session = $session;
        $this->message = $message;
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
        $request = $request->withAttribute(self::MESSAGE_NAME, $this->message);

        return $handler->handle($request);
    }
}
