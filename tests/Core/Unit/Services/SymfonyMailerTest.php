<?php

namespace Tests\Core\Unit\Services;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Symfony\Component\Mime\Email;
use WScore\Deca\Contracts\MailableInterface;
use WScore\Deca\Contracts\ViewInterface;
use WScore\Deca\Services\SymfonyMailer;

class SymfonyMailerTest extends TestCase
{
    public function testBuildMail(): void
    {
        $symfonyMailerMock = $this->createMock(SymfonyMailerInterface::class);
        $mailer = new SymfonyMailer($symfonyMailerMock);

        $mailable = $this->createMock(MailableInterface::class);
        $mailable->method('subject')->willReturn('Test Subject');
        $mailable->method('render')->willReturn('<h1>Hello</h1>');
        $mailable->method('mailTo')->willReturn(['to@example.com' => 'To Name']);
        $mailable->method('from')->willReturn(['from@example.com' => 'From Name']);
        $mailable->method('replyTo')->willReturn(['reply@example.com']);
        $mailable->method('cc')->willReturn(['cc@example.com']);
        $mailable->method('bcc')->willReturn(['bcc@example.com' => 'Bcc Name']);

        // Use reflection to access protected buildMail for testing its logic
        $reflection = new \ReflectionClass(SymfonyMailer::class);
        $method = $reflection->getMethod('buildMail');

        /** @var Email $email */
        $email = $method->invoke($mailer, $mailable);

        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals('Test Subject', $email->getSubject());
        $this->assertEquals('<h1>Hello</h1>', $email->getHtmlBody());
        
        $this->assertEquals('To Name', $email->getTo()[0]->getName());
        $this->assertEquals('to@example.com', $email->getTo()[0]->getAddress());
        
        $this->assertEquals('From Name', $email->getFrom()[0]->getName());
        $this->assertEquals('from@example.com', $email->getFrom()[0]->getAddress());
        
        $this->assertEquals('reply@example.com', $email->getReplyTo()[0]->getAddress());
        $this->assertEquals('cc@example.com', $email->getCc()[0]->getAddress());
        $this->assertEquals('Bcc Name', $email->getBcc()[0]->getName());
    }

    public function testRenderWithView(): void
    {
        $symfonyMailerMock = $this->createMock(SymfonyMailerInterface::class);
        $viewMock = $this->createMock(ViewInterface::class);
        $mailer = new SymfonyMailer($symfonyMailerMock, $viewMock);

        $mailable = $this->createMock(MailableInterface::class);
        $mailable->method('render')->willReturn(''); // No direct render
        $mailable->method('template')->willReturn('mail.twig');
        $mailable->method('data')->willReturn(['name' => 'World']);

        $viewMock->expects($this->once())
            ->method('drawTemplate')
            ->with('mail.twig', ['name' => 'World'])
            ->willReturn('Rendered Content');

        $reflection = new \ReflectionClass(SymfonyMailer::class);
        $method = $reflection->getMethod('render');

        $content = $method->invoke($mailer, $mailable);
        $this->assertEquals('Rendered Content', $content);
    }

    public function testSend(): void
    {
        $symfonyMailerMock = $this->createMock(SymfonyMailerInterface::class);
        $mailer = new SymfonyMailer($symfonyMailerMock);

        $mailable = $this->createMock(MailableInterface::class);
        $mailable->method('subject')->willReturn('Subject');
        $mailable->method('render')->willReturn('Body');
        $mailable->method('mailTo')->willReturn(['to@example.com']);

        $symfonyMailerMock->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(Email::class));

        $mailer->send($mailable);
    }
}
