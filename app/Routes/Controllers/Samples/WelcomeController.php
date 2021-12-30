<?php
declare(strict_types=1);

namespace App\Routes\Controllers\Samples;


use App\Routes\Utils\AbstractController;
use Psr\Http\Message\ResponseInterface;

class WelcomeController extends AbstractController
{
    /**
     * @param string $name
     * @return ResponseInterface
     */
    protected function onGet(string $name): ResponseInterface
    {
        return $this->view('samples/welcome.twig', [
            'name' => $name,
        ]);
    }

}