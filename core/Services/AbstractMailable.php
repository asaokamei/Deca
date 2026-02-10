<?php
declare(strict_types=1);

namespace WScore\Deca\Services;

use WScore\Deca\Interfaces\MailableInterface;

abstract class AbstractMailable implements MailableInterface
{
    public function subject(): string
    {
        return '';
    }

    public function mailTo(): array
    {
        return [];
    }

    public function from(): array
    {
        return [];
    }

    public function replyTo(): array
    {
        return [];
    }

    public function cc(): array
    {
        return [];
    }

    public function bcc(): array
    {
        return [];
    }

    public function template(): string
    {
        return '';
    }

    public function data(): array
    {
        return [];
    }

    public function render(): string
    {
        return '';
    }
}
