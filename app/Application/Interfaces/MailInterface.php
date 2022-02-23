<?php
declare(strict_types=1);

namespace App\Application\Interfaces;

interface MailInterface
{
    public function subject(): string;

    /**
     * returns mail address to send to.
     * ex1: ['mailTo@example.com' => 'mail to name']
     * ex2: ['mailTo@example.com']
     *
     * @return string[]
     */
    public function mailTo(): array;

    /**
     * returns from mail address.
     * ex1: ['from@example.com' => 'mail from']
     * ex2: ['from@example.com']
     *
     * @return string[]
     */
    public function from(): array;


    /**
     * returns reply-to mail address.
     * ex1: ['replyTo@example.com' => 'replyTo mail']
     * ex2: ['replyTo@example.com']
     *
     * @return string[]
     */
    public function replyTo(): array;

    /**
     * returns list of cc mail addresses.
     * ex1: ['cc1@example.com' => 'cc mail1',
     *       'cc2@example.com' => 'cc mail2']
     * ex2: ['cc1@example.com', 'cc2@example.com']
     *
     * @return string[][]
     */
    public function cc(): array;

    /**
     * returns list of bcc mail addresses.
     * ex1: ['bcc1@example.com' => 'bcc mail1',
     *       'bcc2@example.com' => 'bcc mail2']
     * ex2: ['bcc1@example.com', 'bcc2@example.com']
     *
     * @return string[][]
     */
    public function bcc(): array;

    /**
     * returns rendered html string.
     *
     * @override
     * @return string
     */
    public function render(): string;

    /**
     * sends this mail.
     *
     * @return mixed
     */
    public function send();
}