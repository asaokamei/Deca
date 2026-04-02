<?php

namespace Tests\Core\Support;

use WScore\Deca\Services\AbstractMailable;

class MailerCasePhpMailer extends AbstractMailable
{
    private string $subject = 'Test Subject';
    private array $to = ['to@example.com' => 'To Name'];
    private array $from = ['from@example.com' => 'From Name'];
    private array $replyTo = ['reply@example.com' => 'Reply Name'];
    private array $cc = ['cc@example.com' => 'CC Name'];
    private array $bcc = ['bcc@example.com' => 'BCC Name'];
    private string $render = '<h1>Hello</h1>';
    private string $template = '';
    private array $data = [];

    public function withSubject(string $subject): self { $this->subject = $subject; return $this; }
    public function withTo(array $to): self { $this->to = $to; return $this; }
    public function withFrom(array $from): self { $this->from = $from; return $this; }
    public function withReplyTo(array $replyTo): self { $this->replyTo = $replyTo; return $this; }
    public function withCc(array $cc): self { $this->cc = $cc; return $this; }
    public function withBcc(array $bcc): self { $this->bcc = $bcc; return $this; }
    public function withRender(string $render): self { $this->render = $render; return $this; }
    public function withTemplate(string $template): self { $this->template = $template; return $this; }
    public function withData(array $data): self { $this->data = $data; return $this; }

    public function subject(): string
    {
        return $this->subject;
    }
    public function mailTo(): array
    {
        return $this->to;
    }
    public function from(): array
    {
        return $this->from;
    }
    public function replyTo(): array
    {
        return $this->replyTo;
    }
    public function cc(): array
    {
        return $this->cc;
    }
    public function bcc(): array
    {
        return $this->bcc;
    }
    public function render(): string
    {
        return $this->render;
    }
    public function template(): string
    {
        return $this->template;
    }
    public function data(): array
    {
        return $this->data;
    }
}