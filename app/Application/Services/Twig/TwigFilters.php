<?php
declare(strict_types=1);


namespace App\Application\Services\Twig;


use ArrayAccess;
use Twig\Markup;

class TwigFilters
{
    /**
     * @param array|ArrayAccess $array
     * @return Markup
     */
    public function filterArrayToString($array): Markup
    {
        return new Markup($this->arrayToString($array), 'UTF-8');
    }

    public function filterMailAddressArray($address, $name = null)
    {
        if (is_string($address)) {
            return $this->formMailAddress($address, $name);
        }
        if (is_iterable($address)) {
            $mail = array_key_first($address);
            $name = $address[$mail];
            return $this->formMailAddress($mail, $name);
        }
        return $this->formMailAddress($address, $name);
    }

    private function formMailAddress($email, $name)
    {
        if (is_numeric($email)) {
            return $name;
        }
        return "{$name} <{$email}>";
    }

    private function arrayToString($value): string
    {
        if (is_iterable($value)) {
            $list = '';
            foreach ($value as $key => $v) {
                $v = $this->arrayToString($v);
                $list .= "<li>{$key}: $v</li>\n";
            }
            return "<ul>{$list}</ul>";
        }
        return $value;
    }
}