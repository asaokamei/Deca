<?php
declare(strict_types=1);

namespace App\Routes\Filters;

use Attribute;
use Psr\Http\Message\ServerRequestInterface;

#[Attribute]
class PostAsArgs implements ControllerArgFilterInterface
{
    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        return array_merge($args, $request->getParsedBody() ?? []);
    }
}