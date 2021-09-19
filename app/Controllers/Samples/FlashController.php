<?php
declare(strict_types=1);


namespace App\Controllers\Samples;


use App\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface;

class FlashController extends AbstractController
{
    protected function determineMethod(): string
    {
        if (isset($this->getArgs()['method'])) {
            return $this->getArgs()['method'];
        }
        return 'get';
    }

    public function onGet(): ResponseInterface
    {
        $this->messages()->addError('This notice is set in onGet method.');
        $this->messages()->addSuccess('This message is set in onGet method.');
        return $this->view('samples/flash.twig', []);
    }

    public function onPage(): ResponseInterface
    {
        $this->messages()->addError('This notice is set in onPage method.');
        $this->messages()->addSuccess('This message is set in onPage method.');
        return $this->view('samples/flash.twig', [
            'method' => 'page',
        ]);
    }

    public function onBack(): ResponseInterface
    {
        $this->messages()->addError('This notice is set in onBack method.');
        $this->messages()->addSuccess('This message is set in onBack method.');
        return $this->redirectToRoute('flashes');
    }
}