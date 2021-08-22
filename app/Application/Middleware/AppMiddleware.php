<?php


namespace App\Application\Middleware;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Slim\App;

class AppMiddleware implements Middleware
{
    const APP_NAME = 'app';

    /**
     * @var App
     */
    private $app;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(App $app, LoggerInterface $logger)
    {
        $this->app = $app;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if ($this->logger) {
            $url = $request->getUri()->__toString();
            $method = $request->getMethod();
            $this->logger->info("{$method} {$url}");
            if ($method === 'POST') {
                $this->logger->debug("POST Data: ", $request->getParsedBody());
            }
        }

        $request = $request->withAttribute(self::APP_NAME, $this->app);

        return $handler->handle($request);
    }

}