<?php
declare(strict_types=1);

namespace App\Routes\Actions\WelcomeSample;

use App\Routes\Utils\AbstractResponder;
use Psr\Http\Message\ResponseInterface;

class WelcomeResponder extends AbstractResponder
{
    public function view(string $name): ResponseInterface
    {
        return $this->respond()->view('samples/welcome.twig', [
            'name' => $name . '!',
        ]);
    }
}