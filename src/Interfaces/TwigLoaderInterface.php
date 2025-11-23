<?php
declare(strict_types=1);
namespace WScore\Deca\Interfaces;

interface TwigLoaderInterface
{
    public function load(Environment $environment): void;
}