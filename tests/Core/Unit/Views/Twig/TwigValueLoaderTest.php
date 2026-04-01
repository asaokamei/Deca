<?php

namespace Tests\Core\Unit\Views\Twig;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use WScore\Deca\Views\FormData;
use WScore\Deca\Views\FormDotted;
use WScore\Deca\Views\Twig\TwigValueLoader;

class TwigValueLoaderTest extends TestCase
{
    public function testLoadAddsFunctions(): void
    {
        $twig = new Environment(new ArrayLoader([]));
        $loader = new TwigValueLoader();
        $loader->load($twig);

        $this->assertNotNull($twig->getFunction('getValue'));
        $this->assertNotNull($twig->getFunction('getError'));
        $this->assertNotNull($twig->getFunction('checkIf'));
    }

    public function testValueFromRequestParsedBody(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['name' => 'John']);

        $loader = new TwigValueLoader();
        $loader->setRequest($request);

        $this->assertEquals('John', $loader->value('name'));
    }

    public function testValueFromSetValues(): void
    {
        $loader = new TwigValueLoader();
        $loader->setValues(['name' => 'Jane'], []);

        $this->assertEquals('Jane', $loader->value('name'));
    }

    public function testValueWithNestedPath(): void
    {
        $loader = new TwigValueLoader();
        $loader->setValues(['user' => ['profile' => ['name' => 'John']]], []);

        $this->assertEquals('John', $loader->value('user[profile][name]'));
    }

    public function testCheckIf(): void
    {
        $loader = new TwigValueLoader();
        $loader->setValues(['roles' => ['admin', 'editor'], 'status' => 'active'], []);

        $this->assertTrue($loader->checkIf('roles', 'admin'));
        $this->assertTrue($loader->checkIf('roles', 'editor'));
        $this->assertFalse($loader->checkIf('roles', 'viewer'));
        $this->assertTrue($loader->checkIf('status', 'active'));
    }

    public function testError(): void
    {
        $loader = new TwigValueLoader();
        
        // No errors set
        $this->assertEquals('', $loader->error('name'));

        // Set errors
        $loader->setValues([], ['name' => 'Required', 'tags' => ['Tag1', 'Tag2']]);

        $this->assertEquals('Required', $loader->error('name'));
        $this->assertEquals('[Required]', $loader->error('name', '[%s]'));
        $this->assertEquals('Tag1<br>\nTag2', $loader->error('tags'));
    }

    public function testSetValuesWithMessageBagInterface(): void
    {
        $loader = new TwigValueLoader();
        $values = new FormData(['name' => 'John']);
        $errors = new FormDotted(['name' => 'Error']);

        $loader->setValues($values, $errors);

        $this->assertEquals('John', $loader->value('name'));
        $this->assertEquals('Error', $loader->error('name'));
    }
}
