<?php

namespace Tests\Routes\Utils;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class InvokeMethodTraitTest extends TestCase
{

    /**
     * @return ResponseInterface
     */
    protected function getResponse(): ResponseInterface
    {
        $factory = new Psr17Factory();
        return $factory->createResponse();
    }

    public function testInvokeNameAndReturnGivenName()
    {
        $response = $this->getResponse();
        $object = new Invoked($response);
        $response = $object->invoke('getName', [
            'name' => 'tested',
        ]);
        $this->checkResponseEquals($response, 'name: tested');
    }

    public function testInvokeWithoutVariableThrowsAnException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $response = $this->getResponse();
        $object = new Invoked($response);
        $response = $object->invoke('getName', [
            'bad' => 'tested',
        ]);
    }

    public function testInvokeWithoutMethodThrowsAnException()
    {
        $this->expectException(\BadMethodCallException::class);

        $response = $this->getResponse();
        $object = new Invoked($response);
        $response = $object->invoke('noSuchMethod', [
            'bad' => 'tested',
        ]);
        $this->checkResponseEquals($response, 'name: tested');
    }

    public function testOptionalValue()
    {
        $response = $this->getResponse();

        $object = new Invoked($response);
        $response = $object->invoke('getOptional', [
            'name' => 'tested',
        ]);
        $this->checkResponseEquals($response, 'option: option');
    }

    /**
     * @param ResponseInterface $response
     * @param string $expected
     */
    protected function checkResponseEquals(ResponseInterface $response, string $expected): void
    {
        $response->getBody()->rewind();
        $returned = $response->getBody()->getContents();
        $this->assertEquals($expected, $returned);
    }
}
