<?php
declare(strict_types=1);

namespace WScore\Deca\Mailer;

use WScore\Deca\Interfaces\MailInterface;
use WScore\Deca\Interfaces\ViewInterface;
use RuntimeException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * an Abstract class for Symfony's Mailer
 */
abstract class AbstractHtmlMail implements MailInterface
{
    public function __construct(
        protected ViewInterface $view,
        protected MailerInterface $mailer
    ) {
    }

    /** @Override */
    /** @noinspection PhpUnhandledExceptionInspection */
    public function render(): string
    {
        // return $this->view->fetch('dummy.twig', []);
        throw new RuntimeException('AbstractHtmlMail::render method not implemented. ');
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

    protected function buildMail(): Email
    {
        $email = (new Email())
            ->subject($this->subject())
            ->html($this->render());

        if ($addresses = $this->listAddress($this->mailTo())) {
            $email->to(...$addresses);
        }
        if ($address = $this->toAddress($this->from())) {
            $email->from($address);
        }
        if ($address = $this->toAddress($this->replyTo())) {
            $email->replyTo($address);
        }
        if ($addresses = $this->listAddress($this->cc())) {
            $email->cc(...$addresses);
        }
        if ($addresses = $this->listAddress($this->bcc())) {
            $email->bcc(...$addresses);
        }
        return $email;
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function send()
    {
        $mail = $this->buildMail();
        $this->mailer->send($mail);
    }
}