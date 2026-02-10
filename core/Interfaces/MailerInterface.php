<?php
declare(strict_types=1);

namespace WScore\Deca\Interfaces;

interface MailerInterface
{
    /**
     * sends the mailable.
     *
     * @param MailableInterface $mailable
     * @return void
     */
    public function send(MailableInterface $mailable): void;
}
