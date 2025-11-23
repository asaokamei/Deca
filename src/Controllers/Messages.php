<?php
declare(strict_types=1);

namespace WScore\Deca\Controllers;

use App\Application\Interfaces\MessageInterface;
use App\Application\Interfaces\SessionInterface;

class Messages implements MessageInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $sessionFactory)
    {
        $this->session = $sessionFactory;
    }

    public function addMessage(string $level, string $message)
    {
        $messages = (array) $this->session->getFlash($level, []);
        $messages[] = $message;
        $this->session->setFlash($level, $messages);
    }

    public function addSuccess(string $message)
    {
        $this->addMessage(self::LEVEL_SUCCESS, $message);
    }

    public function addError(string $message)
    {
        $this->addMessage(self::LEVEL_ERROR, $message);
    }

    public function getMessages(string $level): array
    {
        $this->session->clearFlash();
        return (array) $this->session->getFlash($level, []);
    }
}