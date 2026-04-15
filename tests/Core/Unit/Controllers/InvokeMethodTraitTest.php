<?php

namespace WScore\Deca\Tests\Controllers;

use BadMethodCallException;
use InvalidArgumentException;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Core\Support\InvokeMethodTraitStub;
use Tests\Core\Support\InvokeMethodTraitStubWithRequest;
use WScore\Deca\Controllers\InvokeMethodTrait;

class InvokeMethodTraitTest extends TestCase
{
    private function createResponse(): ResponseInterface
    {
        return (new Psr17Factory())->createResponse();
    }

    private function createRequest(): ServerRequestInterface
    {
        return (new Psr17Factory())->createServerRequest('GET', 'http://example.com');
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
        $this->expectException(InvalidArgumentException::class);

        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $stub->invoke('getName', ['bad' => 'tested']);
    }

    public function testInvokeWithoutMethodThrowsAnException(): void
    {
        $this->expectException(BadMethodCallException::class);

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

    public function testInvokeWithNoParameters(): void
    {
        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $response = $stub->invoke('getNoParams', []);
        $response->getBody()->rewind();
        $this->assertEquals('no params', $response->getBody()->getContents());
    }

    public function testInvokeWithArrayParameter(): void
    {
        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $args = ['items' => ['item1', 'item2', 'item3']];
        $response = $stub->invoke('getArray', $args);
        $response->getBody()->rewind();
        $this->assertEquals('array: item1, item2, item3', $response->getBody()->getContents());
    }

    public function testInvokeWithMultipleParameters(): void
    {
        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $response = $stub->invoke('getMultiple', [
            'first' => 'value1',
            'second' => 'value2',
        ]);
        $response->getBody()->rewind();
        $this->assertEquals('first: value1, second: value2', $response->getBody()->getContents());
    }

    public function testInvokeWithMixedParameters(): void
    {
        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $response = $stub->invoke('getMixed', [
            'name' => 'Alice',
            'age' => 25,
            'active' => true,
        ]);
        $response->getBody()->rewind();
        $this->assertEquals('name: Alice, age: 25, active: true', $response->getBody()->getContents());
    }

    public function testInvokeWithAttributeFromRequest(): void
    {
        $response = $this->createResponse();
        $request = $this->createRequest();
        $request = $request->withAttribute('attribute', 'custom_value');

        $stub = new InvokeMethodTraitStubWithRequest($response, $request);
        $response = $stub->invoke('getAttribute', []);
        $response->getBody()->rewind();
        $this->assertEquals('attribute: custom_value', $response->getBody()->getContents());
    }

    public function testInvokeWithArgAndAttributePriority(): void
    {
        $response = $this->createResponse();
        $request = $this->createRequest();
        $request = $request->withAttribute('name', 'attribute_value');

        $stub = new InvokeMethodTraitStubWithRequest($response, $request);
        $response = $stub->invoke('getName', ['name' => 'arg_value']);
        $response->getBody()->rewind();
        $this->assertEquals('name: arg_value', $response->getBody()->getContents());
    }

    public function testInvokeWithOptionalAndAttribute(): void
    {
        $response = $this->createResponse();
        $request = $this->createRequest();
        $request = $request->withAttribute('option', 'attribute_option');

        $stub = new InvokeMethodTraitStubWithRequest($response, $request);
        $response = $stub->invoke('getOptional', []);
        $response->getBody()->rewind();
        $this->assertEquals('option: attribute_option', $response->getBody()->getContents());
    }

    public function testInvokeWithArrayAndOptionalParameters(): void
    {
        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $response = $stub->invoke('getArrayOptional', [
            'items' => ['item1', 'item2'],
            'name' => 'test',
        ]);
        $response->getBody()->rewind();
        $this->assertEquals('array: item1, item2, name: test', $response->getBody()->getContents());
    }

    public function testInvokeWithEmptyArgs(): void
    {
        $response = $this->createResponse();
        $stub = new InvokeMethodTraitStub($response);
        $response = $stub->invoke('getNoParams', []);
        $response->getBody()->rewind();
        $this->assertEquals('no params', $response->getBody()->getContents());
    }
}
