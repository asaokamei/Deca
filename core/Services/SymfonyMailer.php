<?php
declare(strict_types=1);

namespace WScore\Deca\Services;

use Symfony\Component\Mailer\Mailer;
use WScore\Deca\Interfaces\MailableInterface;
use WScore\Deca\Interfaces\MailerInterface;
use WScore\Deca\Interfaces\ViewInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * a Mailer implementation using Symfony's Mailer
 */
class SymfonyMailer implements MailerInterface
{
    public function __construct(
        protected Mailer $mailer,
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

    protected function toAddress(array $address): ?Address
    {
        if (empty($address)) {
            return null;
        }
        $email = array_key_first($address);
        $name = $address[$email];

        if (is_numeric($email)) {
            if ($name === null) {
                return null;
            }
            $email = $name;
            $name = '';
        }
        return new Address($email, $name);
    }

    /**
     * @param array $list
     * @return Address[]
     */
    protected function listAddress(array $list): array
    {
        $addresses = [];
        foreach ($list as $email => $name) {
            if ($address = $this->toAddress([$email => $name])) {
                $addresses[] = $address;
            }
        }
        return $addresses;
    }

    protected function buildMail(MailableInterface $mailable): Email
    {
        $email = (new Email())
            ->subject($mailable->subject())
            ->html($this->render($mailable));

        if ($addresses = $this->listAddress($mailable->mailTo())) {
            $email->to(...$addresses);
        }
        if ($address = $this->toAddress($mailable->from())) {
            $email->from($address);
        }
        if ($address = $this->toAddress($mailable->replyTo())) {
            $email->replyTo($address);
        }
        if ($addresses = $this->listAddress($mailable->cc())) {
            $email->cc(...$addresses);
        }
        if ($addresses = $this->listAddress($mailable->bcc())) {
            $email->bcc(...$addresses);
        }
        return $email;
    }

    public function send(MailableInterface $mailable): void
    {
        $mail = $this->buildMail($mailable);
        $this->mailer->send($mail);
    }
}
