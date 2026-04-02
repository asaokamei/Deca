<?php
declare(strict_types=1);

namespace tests\Core\Unit\Handlers;

use PHPUnit\Framework\TestCase;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Exception;
use WScore\Deca\Contracts\ViewInterface;
use WScore\Deca\Handlers\SimpleErrorHandler;

class SimpleErrorHandlerTest extends TestCase
{
    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ViewInterface
     */
    private function createViewMock()
    {
        return $this->createMock(ViewInterface::class);
    }

    public function test_invoke_returns_rendered_template()
    {
        $view = $this->createViewMock();
        $exception = new Exception("Test Exception");
        $displayErrorDetails = true;

        $view->expects($this->once())
            ->method('drawTemplate')
            ->with('layouts/error.twig', [
                'title' => 'Error Reported',
                'exception' => $exception,
                'displayErrorDetails' => $displayErrorDetails
            ])
            ->willReturn('Rendered Error Page');

        $handler = new SimpleErrorHandler($view);
        $result = $handler($exception, $displayErrorDetails);

        $this->assertEquals('Rendered Error Page', $result);
    }

    public function test_invoke_with_HttpNotFoundException()
    {
        $view = $this->createViewMock();
        $request = $this->createMock(ServerRequestInterface::class);
        $exception = new HttpNotFoundException($request, "Not Found");
        
        $view->expects($this->once())
            ->method('setRequest')
            ->with($request);

        $view->expects($this->once())
            ->method('drawTemplate')
            ->with($this->anything(), $this->callback(function($data) {
                return $data['title'] === 'URL Not Found';
            }))
            ->willReturn('404 Page');

        $handler = new SimpleErrorHandler($view);
        $result = $handler($exception, false);

        $this->assertEquals('404 Page', $result);
    }

    public function test_invoke_with_HttpForbiddenException()
    {
        $view = $this->createViewMock();
        $request = $this->createMock(ServerRequestInterface::class);
        $exception = new HttpForbiddenException($request, "Forbidden");

        $view->expects($this->once())
            ->method('drawTemplate')
            ->with($this->anything(), $this->callback(function($data) {
                return $data['title'] === 'Access Not Allowed';
            }))
            ->willReturn('403 Page');

        $handler = new SimpleErrorHandler($view);
        $handler($exception, false);
    }

    public function test_invoke_with_HttpMethodNotAllowedException()
    {
        $view = $this->createViewMock();
        $request = $this->createMock(ServerRequestInterface::class);
        $exception = new HttpMethodNotAllowedException($request, "Method Not Allowed");

        $view->expects($this->once())
            ->method('drawTemplate')
            ->with($this->anything(), $this->callback(function($data) {
                return $data['title'] === 'Not Available URL';
            }))
            ->willReturn('405 Page');

        $handler = new SimpleErrorHandler($view);
        $handler($exception, false);
    }

    public function test_invoke_handles_view_exception()
    {
        $view = $this->createViewMock();
        $exception = new Exception("Original Exception");
        
        $view->method('drawTemplate')
            ->willThrowException(new Exception("View Drawing Failed"));

        $handler = new SimpleErrorHandler($view);
        $result = $handler($exception, false);

        $this->assertEquals('Error Reported: View Drawing Failed', $result);
    }
}
