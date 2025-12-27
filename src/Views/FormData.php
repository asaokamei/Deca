<?php

namespace WScore\Deca\Views;

use ArrayAccess;

class FormData
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
     * 多次元配列からドット記法で値を取得するロジック
     */
    public function getByPath(string $path)
    {
        $array = $this->oldData;
        if (!$path) return $array;

        foreach (explode('.', $path) as $segment) {
            if (is_array($array) || $array instanceof ArrayAccess) {
                if (array_key_exists($segment, $array)) {
                    $array = $array[$segment];
                } else {
                    return null;
                }
            } elseif (is_object($array)) {
                if (method_exists($array, $segment)) {
                    $array = $array->{$segment}();
                } elseif (method_exists($array, 'get' . ucfirst($segment))) {
                    $array = $array->{'get' . ucfirst($segment)}();
                } elseif (method_exists($array, 'get')) {
                    $array = $array->get($segment);
                } elseif (property_exists($array, $segment)) {
                    $array = $array->{$segment};
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
        return $array;
    }

    /**
     * "dev[program][php]" -> "dev.program.php" に変換して値を引く
     */
    public function getByName(string $name)
    {
        // 1. "[]" を除去 (tags[] のような末尾配列対応)
        $path = str_replace('[]', '', $name);

        // 2. "[" を "." に置換し、 "]" を除去
        // 例: "dev[program][php]" -> "dev.program.php"
        $path = str_replace(']', '', $path);
        $path = str_replace('[', '.', $path);

        // 3. 既存のドット記法用検索ロジックに投げる
        return $this->getByPath($path);
    }
}