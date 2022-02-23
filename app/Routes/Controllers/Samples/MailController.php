<?php

namespace App\Routes\Controllers\Samples;

use App\Routes\Mails\SampleMailer;
use App\Routes\Utils\AbstractController;
use Psr\Http\Message\ResponseInterface;

class MailController extends AbstractController
{
    private SampleMailer $mailer;

    public function __construct(SampleMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function onGet(): ResponseInterface
    {
        return $this->view('samples/mail-form.twig', [
            'mailer' => $this->mailer,
        ]);
    }

    public function onPost(): ResponseInterface
    {
        $this->mailer->send();
        return $this->redirect()->toRoute('mail');
    }
}