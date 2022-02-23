<?php
declare(strict_types=1);

namespace App\Routes\Mails;

class SampleMailer extends AbstractHtmlMail
{
    public function subject(): string
    {
        return 'Test Subject';
    }

    public function mailTo(): array
    {
        return ['to@example.com' => 'to mail'];
    }

    public function from(): array
    {
        return ['from@example.com'];
    }

    public function replyTo(): array
    {
        return ['replyTo@example.com'];
    }

    public function cc(): array
    {
        return [
            'cc1@example.com' => 'cc1',
            'cc2@example.com' => 'cc2',
        ];
    }

    public function bcc(): array
    {
        return [
            'bcc1@example.com',
            'bcc2@example.com',
        ];
    }

    public function render(): string
    {
        return "<h1>Test Mailer</h1><p>rendered by SampleMailer class.</p>";
    }
}