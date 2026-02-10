<?php
declare(strict_types=1);

namespace WScore\Deca\Services;

use PHPMailer\PHPMailer\PHPMailer as PHPMailerEngine;
use PHPMailer\PHPMailer\Exception;
use WScore\Deca\Interfaces\MailableInterface;
use WScore\Deca\Interfaces\MailerInterface;
use WScore\Deca\Interfaces\ViewInterface;
use RuntimeException;

/**
 * a Mailer implementation using PHPMailer
 */
class PhpMailer implements MailerInterface
{
    public function __construct(
        protected PHPMailerEngine $mailer,
        protected ?ViewInterface $view = null
    ) {
    }

    protected function render(MailableInterface $mailable): string
    {
        $html = $mailable->render();
        if ($html) {
            return $html;
        }
        if ($this->view && $template = $mailable->template()) {
            return $this->view->drawTemplate($template, $mailable->data());
        }
        return '';
    }

    protected function setAddresses(array $list, string $type = 'to'): void
    {
        foreach ($list as $email => $name) {
            if (is_numeric($email)) {
                $email = $name;
                $name = '';
            }
            try {
                match ($type) {
                    'to' => $this->mailer->addAddress($email, $name),
                    'cc' => $this->mailer->addCC($email, $name),
                    'bcc' => $this->mailer->addBCC($email, $name),
                    'replyTo' => $this->mailer->addReplyTo($email, $name),
                };
            } catch (Exception $e) {
                throw new RuntimeException("Invalid address: {$email}", 0, $e);
            }
        }
    }

    protected function setFromAddress(array $address): void
    {
        if (empty($address)) {
            return;
        }
        $email = array_key_first($address);
        $name = $address[$email];

        if (is_numeric($email)) {
            $email = $name;
            $name = '';
        }
        try {
            $this->mailer->setFrom($email, $name);
        } catch (Exception $e) {
            throw new RuntimeException("Invalid from address: {$email}", 0, $e);
        }
    }

    protected function buildMail(MailableInterface $mailable): void
    {
        $this->mailer->isHTML(true);
        $this->mailer->Subject = $mailable->subject();
        $this->mailer->Body = $this->render($mailable);

        $this->setAddresses($mailable->mailTo(), 'to');
        $this->setFromAddress($mailable->from());
        $this->setAddresses($mailable->replyTo(), 'replyTo');
        $this->setAddresses($mailable->cc(), 'cc');
        $this->setAddresses($mailable->bcc(), 'bcc');
    }

    public function send(MailableInterface $mailable): void
    {
        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->mailer->clearCustomHeaders();
        $this->mailer->clearReplyTos();

        $this->buildMail($mailable);
        try {
            if (!$this->mailer->send()) {
                throw new RuntimeException("Mailer Error: " . $this->mailer->ErrorInfo);
            }
        } catch (Exception $e) {
            throw new RuntimeException("Mailer Exception: " . $e->getMessage(), 0, $e);
        }
    }
}
