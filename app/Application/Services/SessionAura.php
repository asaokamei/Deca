<?php
declare(strict_types=1);


namespace App\Application\Services;


use App\Application\Interfaces\SessionInterface;
use Aura\Session\Segment;
use Aura\Session\Session;
use Aura\Session\SessionFactory;

class SessionAura implements SessionInterface
{
    private $csrf_token_name = '_csrf_token';
    /**
     * @var SessionFactory
     */
    private $sessionFactory;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var Segment
     */
    private $segment;

    public function __construct(SessionFactory $sessionFactory)
    {
        $this->sessionFactory = $sessionFactory;
        $this->session = $this->sessionFactory->newInstance($_COOKIE);
        $this->segment = $this->session->getSegment('app');
    }

    public function validateCsRfToken(string $token): bool
    {
        return $this->session->getCsrfToken()->isValid($token);
    }

    public function getCsRfToken(): string
    {
        return $this->session->getCsrfToken()->getValue();
    }

    public function getCsRfTokenName(): string
    {
        return $this->csrf_token_name;
    }

    public function regenerateCsRfToken(): void
    {
        $this->session->getCsrfToken()->regenerateValue();
    }

    public function getFlash(string $key, $default = null)
    {
        return $this->segment->getFlash($key, $default);
    }

    public function setFlash(string $key, $val)
    {
        $this->segment->setFlashNow($key, $val);
    }

    public function clearFlash()
    {
        $this->segment->clearFlash();
    }

    public function save(string $key, $val)
    {
        $this->segment->set($key, $val);
    }

    public function load($key)
    {
        return $this->segment->get($key);
    }

    /**
     * @param string $csrf_token_name
     * @return SessionAura
     */
    public function setCsrfTokenName(string $csrf_token_name): SessionAura
    {
        $this->csrf_token_name = $csrf_token_name;
        return $this;
    }
}