<?php
declare(strict_types=1);

namespace App\Routes\Actions\WelcomeSample;


use App\Routes\Utils\AbstractAction;
use Psr\Http\Message\ResponseInterface;

class WelcomeAction extends AbstractAction
{
    /**
     * @var WelcomeResponder
     */
    private $responder;

    public function __construct(WelcomeResponder $responder)
    {
        $this->responder = $responder;
    }

    /**
     * @param string $name
     * @return ResponseInterface
     */
    public function action(string $name): ResponseInterface
    {
        return $this->responder
            ->set($this->getRequest(), $this->getResponse())
            ->view($name);
    }
}