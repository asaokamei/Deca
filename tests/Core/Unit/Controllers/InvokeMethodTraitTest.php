<?php

namespace Tests\Core\Unit\Controllers;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tests\Support\InvokeMethodTraitStub;

class InvokeMethodTraitTest extends TestCase
{
    private function createResponse(): ResponseInterface
    {
        return (new Psr17Factory())->createResponse();
    }

    public function testInvokeNameAndReturnGivenName(): void
    {
        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $response = $stub->invoke('getName', ['name' => 'tested']);
        $response->getBody()->rewind();
        $this->assertEquals('name: tested', $response->getBody()->getContents());
    }

    public function testInvokeWithoutVariableThrowsAnException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $stub->invoke('getName', ['bad' => 'tested']);
    }

    public function testInvokeWithoutMethodThrowsAnException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $stub->invoke('noSuchMethod', ['bad' => 'tested']);
    }

    public function testOptionalValue(): void
    {
        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $response = $stub->invoke('getOptional', ['name' => 'tested']);
        $response->getBody()->rewind();
        $this->assertEquals('option: option', $response->getBody()->getContents());
    }

    public function testOptionalValueWithArgument(): void
    {
        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $response = $stub->invoke('getOptional', ['option' => 'custom']);
        $response->getBody()->rewind();
        $this->assertEquals('option: custom', $response->getBody()->getContents());
    }
}
