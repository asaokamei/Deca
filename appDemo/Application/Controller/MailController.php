<?php

namespace AppDemo\Application\Controller;

use AppDemo\Application\Emails\SampleMail;
use Psr\Http\Message\ResponseInterface;
use WScore\Deca\Controllers\AbstractController;
use WScore\Deca\Contracts\MailerInterface;

class MailController extends AbstractController
{
    public function __construct(
        private SampleMail $mail,
        private MailerInterface $mailer
    )
    {
    }

    public function onGet(): ResponseInterface
    {
        return $this->view('samples/mail-form.twig', [
            'mailer' => $this->mail,
        ]);
    }
}