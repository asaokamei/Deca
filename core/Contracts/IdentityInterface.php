<?php

declare(strict_types=1);

namespace WScore\Deca\Contracts;

/**
 * Read-only snapshot of the authenticated principal for the current request.
 *
 * Store on the request under {@see self::class} (FQCN attribute key). For guests,
 * set the attribute to null.
 */
interface IdentityInterface
{
    public function getId(): string;

    public function getDisplayName(): string;

    /**
     * @return string[]
     */
    public function getRoles(): array;
}
