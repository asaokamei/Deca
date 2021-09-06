<?php

namespace App\Application\Services;

use App\Application\Interfaces\MessageInterface;
use Aura\Session\Segment;
use Aura\Session\SessionFactory;

class MessageAura implements MessageInterface
{
    /**
     * @var Segment
     */
    private $session;

    public function __construct(SessionFactory $sessionFactory)
    {
        $this->session = $sessionFactory->newInstance($_COOKIE)->getSegment('message');
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