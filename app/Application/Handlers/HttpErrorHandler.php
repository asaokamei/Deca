<?php
declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Interfaces\ViewInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class HttpErrorHandler extends ErrorHandler
{
    /**
     * @var ViewInterface
     */
    private $view;

    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        ViewInterface $view,
        LoggerInterface $logger
    ) {
        parent::__construct($callableResolver, $responseFactory);
        $this->view = $view;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    protected function respond(): Response
    {
        $exception = $this->exception;
        $statusCode = $this->exception->getCode();

        $response = $this->responseFactory->createResponse($statusCode);
        $title = $exception instanceof HttpException
            ? $exception->getTitle()
            : get_class($exception);

        $this->logger->error($title, ['file' => $exception->getFile(), 'line' => $exception->getLine()]);

        if ($this->displayErrorDetails) {
            $whoops = new Run();
            $whoops->pushHandler(new PrettyPageHandler());
            $response->getBody()->write($whoops->handleException($exception));
            return $response;
        }
        return $this->view->render($response, 'error.twig', [
            'title' => $title,
        ]);
    }
}
