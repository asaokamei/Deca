<?php

namespace Tests\Core\Unit\Services;

use Tests\Core\Support\MailerCasePhpMailer;
use PHPMailer\PHPMailer\PHPMailer as PHPMailerEngine;
use PHPUnit\Framework\TestCase;
use WScore\Deca\Contracts\ViewInterface;
use WScore\Deca\Services\PhpMailer;

class PhpMailerTest extends TestCase
{
    public function testWithAbstractMailer(): void
    {
        $phpMailerEngine = $this->getMockBuilder(PHPMailerEngine::class)
            ->onlyMethods(['send'])
            ->getMock();
        $phpMailerEngine->method('send')->willReturn(true);

        $mailer = new PhpMailer($phpMailerEngine);
        $mailable = new MailerCasePhpMailer();
        $mailer->send($mailable);

        $reflection = new \ReflectionClass(PhpMailer::class);
        $mailerProperty = $reflection->getProperty('mailer');
        $mailerProperty->setAccessible(true);
        /** @var PHPMailerEngine $engine */
        $engine = $mailerProperty->getValue($mailer);

        $this->assertEquals('Test Subject', $engine->Subject);
        $this->assertEquals('<h1>Hello</h1>', $engine->Body);
    }
    public function testBuildMail(): void
    {
        $phpMailerEngine = new PHPMailerEngine();
        $mailer = new PhpMailer($phpMailerEngine);

        $mailable = (new MailerCasePhpMailer())
            ->withSubject('Test Subject')
            ->withRender('<h1>Hello</h1>')
            ->withTo(['to@example.com' => 'To Name'])
            ->withFrom(['from@example.com' => 'From Name'])
            ->withReplyTo(['reply@example.com'])
            ->withCc(['cc@example.com'])
            ->withBcc(['bcc@example.com' => 'Bcc Name']);

        // Use reflection to access protected buildMail for testing its logic
        $reflection = new \ReflectionClass(PhpMailer::class);
        $method = $reflection->getMethod('buildMail');
        $method->invoke($mailer, $mailable);

        $this->assertEquals('Test Subject', $phpMailerEngine->Subject);
        $this->assertEquals('<h1>Hello</h1>', $phpMailerEngine->Body);
        $this->assertEquals('text/html', $phpMailerEngine->ContentType);

        $allTo = $phpMailerEngine->getToAddresses();
        $this->assertCount(1, $allTo);
        $this->assertEquals('to@example.com', $allTo[0][0]);
        $this->assertEquals('To Name', $allTo[0][1]);

        $this->assertEquals('from@example.com', $phpMailerEngine->From);
        $this->assertEquals('From Name', $phpMailerEngine->FromName);

        $allCc = $phpMailerEngine->getCcAddresses();
        $this->assertCount(1, $allCc);
        $this->assertEquals('cc@example.com', $allCc[0][0]);

        $allBcc = $phpMailerEngine->getBccAddresses();
        $this->assertCount(1, $allBcc);
        $this->assertEquals('bcc@example.com', $allBcc[0][0]);
        $this->assertEquals('Bcc Name', $allBcc[0][1]);

        $allReplyTo = $phpMailerEngine->getReplyToAddresses();
        $this->assertCount(1, $allReplyTo);
        // Debug: print_r($allReplyTo);
        $this->assertEquals('reply@example.com', $allReplyTo[0][0]);
    }

    public function testRenderWithView(): void
    {
        $phpMailerEngine = $this->createMock(PHPMailerEngine::class);
        $viewMock = $this->createMock(ViewInterface::class);
        $mailer = new PhpMailer($phpMailerEngine, $viewMock);

        $mailable = (new MailerCasePhpMailer())
            ->withRender('') // No direct render
            ->withTemplate('mail.twig')
            ->withData(['name' => 'World']);

        $viewMock->expects($this->once())
            ->method('drawTemplate')
            ->with('mail.twig', ['name' => 'World'])
            ->willReturn('Rendered Content');

        $reflection = new \ReflectionClass(PhpMailer::class);
        $method = $reflection->getMethod('render');

        $content = $method->invoke($mailer, $mailable);
        $this->assertEquals('Rendered Content', $content);
    }

    public function testSend(): void
    {
        $phpMailerEngine = $this->getMockBuilder(PHPMailerEngine::class)
            ->onlyMethods(['send'])
            ->getMock();
        $mailer = new PhpMailer($phpMailerEngine);

        $mailable = (new MailerCasePhpMailer())
            ->withSubject('Subject')
            ->withRender('Body')
            ->withTo(['to@example.com']);

        $phpMailerEngine->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $mailer->send($mailable);
        
        $this->assertEquals('Subject', $phpMailerEngine->Subject);
        $this->assertEquals('Body', $phpMailerEngine->Body);
    }

    public function testSendThrowsExceptionOnFailure(): void
    {
        $phpMailerEngine = $this->getMockBuilder(PHPMailerEngine::class)
            ->onlyMethods(['send'])
            ->getMock();
        $phpMailerEngine->ErrorInfo = 'Mock Error';
        $mailer = new PhpMailer($phpMailerEngine);

        $mailable = (new MailerCasePhpMailer())
            ->withSubject('Subject')
            ->withRender('Body')
            ->withTo(['to@example.com']);

        $phpMailerEngine->expects($this->once())
            ->method('send')
            ->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Mailer Error: Mock Error');

        $mailer->send($mailable);
    }
}
