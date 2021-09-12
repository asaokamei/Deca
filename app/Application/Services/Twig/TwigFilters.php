<?php
declare(strict_types=1);


namespace App\Application\Services\Twig;


use Twig\Markup;

class TwigFilters
{
    /**
     * @param array|\ArrayAccess $array
     * @return Markup
     */
    public function filterArrayToString($array): Markup
    {
        return new Markup($this->arrayToString($array), 'UTF-8');
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