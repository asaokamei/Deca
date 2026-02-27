<?php

namespace WScore\Deca\Views;

/**
 * Form data backed by a flat array with dot-notation keys (e.g. Laravel-style errors).
 * Same API as FormData; use for error bags where keys are "profile.email", "dev.ai.0", etc.
 */
class FormDotted
{
    public function __construct(private array $oldData)
    {
    }

    /**
     * 値がPathと一致するか、あるいは配列に含まれるか
     */
    public function checkIf(string $path, string $value): bool
    {
        if (str_contains($path, '[')) {
            $found = $this->getByName($path);
        } else {
            $found = $this->getByPath($path);
        }
        if (is_array($found)) {
            return in_array($value, $found);
        }
        return $found === $value;
    }

    /**
     * Dot-key lookup: exact key first, then keys under path (e.g. path.0, path.1) as array.
     */
    public function getByPath(string $path): mixed
    {
        if ($path === '') {
            return $this->oldData;
        }
        if (array_key_exists($path, $this->oldData)) {
            return $this->oldData[$path];
        }
        $prefix = $path . '.';
        $collected = [];
        foreach ($this->oldData as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $collected[] = $value;
            }
        }
        if ($collected === []) {
            return null;
        }
        return count($collected) === 1 ? $collected[0] : $collected;
    }

    /**
     * "dev[program][php]" -> "dev.program.php" に変換してドットキーで引く
     */
    public function getByName(string $name): mixed
    {
        $path = str_replace('[]', '', $name);
        $path = str_replace(']', '', $path);
        $path = str_replace('[', '.', $path);
        return $this->getByPath($path);
    }
}
