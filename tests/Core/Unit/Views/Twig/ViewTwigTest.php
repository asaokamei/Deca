<?php

namespace Tests\Core\Unit\Views\Twig;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use WScore\Deca\Views\Twig\TwigLoaderInterface;
use WScore\Deca\Views\Twig\ViewTwig;

class ViewTwigTest extends TestCase
{
    public function testRender(): void
    {
        $loader = new ArrayLoader([
            'test.twig' => 'Hello {{ name }}!',
        ]);
        $twig = new Environment($loader);
        $view = new ViewTwig($twig);

        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $response->method('getBody')->willReturn($body);
        $body->expects($this->once())
            ->method('write')
            ->with('Hello World!');

        $result = $view->render($response, 'test.twig', ['name' => 'World']);
        $this->assertSame($response, $result);
    }

    public function testAddDefaultVariables(): void
    {
        $loader = new ArrayLoader([
            'test.twig' => '{{ global_var }} {{ local_var }}',
        ]);
        $twig = new Environment($loader);
        $view = new ViewTwig($twig, ['global_var' => 'Global']);
        $view->add('another_global', 'Another');

        $output = $view->drawTemplate('test.twig', ['local_var' => 'Local']);
        $this->assertEquals('Global Local', $output);
    }

    public function testLoadersAreCalled(): void
    {
        $loader = new ArrayLoader([
            'test.twig' => 'test',
        ]);
        $twig = new Environment($loader);
        $view = new ViewTwig($twig);

        $request = $this->createMock(ServerRequestInterface::class);
        $view->setRequest($request);

        $twigLoader = $this->createMock(TwigLoaderInterface::class);
        $twigLoader->expects($this->once())
            ->method('setRequest')
            ->with($request);
        $twigLoader->expects($this->once())
            ->method('load')
            ->with($twig);

        $view->setRuntimeLoader($twigLoader);

        // First draw calls load
        $view->drawTemplate('test.twig');

        // Second draw should NOT call load again (isLoaded check)
        $view->drawTemplate('test.twig');
    }

    public function testSetInputs(): void
    {
        $loader = new ArrayLoader([
            'test.twig' => '{{ getValue("name") }}',
        ]);
        $twig = new Environment($loader);
        $view = new ViewTwig($twig);

        $view->setInputs(['name' => 'John'], []);

        $output = $view->drawTemplate('test.twig');
        $this->assertEquals('John', $output);
    }
}
