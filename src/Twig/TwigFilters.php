<?php
declare(strict_types=1);
namespace WScore\Deca\Twig;

class TwigFilters implements TwigLoaderInterface
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function load(Environment $environment): void
    {
        $environment->addFilter(new TwigFilter('arrayToString', [$this, 'filterArrayToString']));
        $environment->addFilter(new TwigFilter('mailAddress', [$this, 'filterMailAddressArray']));
    }

    public function filterArrayToString($array): string
    {
        return json_encode($array);
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
}