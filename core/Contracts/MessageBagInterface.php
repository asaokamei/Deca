<?php

namespace WScore\Deca\Contracts;


/**
 * Form data backed by a flat array with dot-notation keys (e.g. Laravel-style errors).
 * Same API as FormData; use for error bags where keys are "profile.email", "dev.ai.0", etc.
 */
interface MessageBagInterface
{
    /**
     * 値がPathと一致するか、あるいは配列に含まれるか
     */
    public function checkIf(string $path, string $value): bool;

    /**
     * Dot-key lookup: the exact key first, then keys under path (e.g. path.0, path.1) as array.
     */
    public function getByPath(string $path): mixed;

    /**
     * "dev[program][php]" -> "dev.program.php" に変換してドットキーで引く
     */
    public function getByName(string $name): mixed;
}