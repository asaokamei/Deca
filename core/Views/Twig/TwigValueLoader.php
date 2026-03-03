<?php

namespace WScore\Deca\Views\Twig;

use Psr\Http\Message\RequestInterface;
use Twig\Environment;
use Twig\TwigFunction;
use WScore\Deca\Contracts\MessageBagInterface;
use WScore\Deca\Views\FormData;
use WScore\Deca\Views\FormDotted;

class TwigValueLoader implements TwigLoaderInterface
{
    private MessageBagInterface $values;
    private ?MessageBagInterface $errors = null;
    private RequestInterface $request;

    public function load(Environment $environment): void
    {
        $environment->addFunction(new TwigFunction('getValue', [$this, 'value']));
        $environment->addFunction(new TwigFunction('getError', [$this, 'error']));
        $environment->addFunction(new TwigFunction('checkIf', [$this, 'checkIf']));
    }

    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

    public function value(string $name): mixed
    {
        if (!isset($this->values)) {
            $this->values = new FormData((array) $this->request->getParsedBody());
        }
        $value = $this->values->getByName($name);

        return $value;
    }

    public function checkIf(string $name, string $value): bool
    {
        if (!isset($this->values)) {
            $this->values = new FormData((array)$this->request->getParsedBody());
        }
        return $this->values->checkIf($name, $value);
    }

    public function error(string $name, string $format = '%s', string $separator = '<br>\n'): string
    {
        if (!isset($this->errors)) {
            return '';
        }
        $value = $this->errors->getByName($name);
        $string = '';
        if (is_string($value)) {
            $string = sprintf($format, $value);
        }
        if (is_array($value)) {
            $string = implode($separator, $value);
        }
        return $string;
    }

    public function setValues(array|MessageBagInterface $values, array|MessageBagInterface $errors): void
    {
        $this->values = $values instanceof MessageBagInterface ? $values: new FormData($values);
        $this->errors = $errors instanceof MessageBagInterface ? $errors: new FormDotted($errors);
    }
}