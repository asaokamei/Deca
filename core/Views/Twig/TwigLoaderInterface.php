<?php
declare(strict_types=1);
namespace WScore\Deca\Views\Twig;

use Psr\Http\Message\RequestInterface;
use Twig\Environment;

interface TwigLoaderInterface
{
    public function load(Environment $environment): void;

    public function setRequest(RequestInterface $request);
}