<?php
declare(strict_types=1);

namespace App\Routes\Filters;

use Attribute;
use Psr\Http\Message\ServerRequestInterface;

#[Attribute]
class PostArray implements ControllerArgFilterInterface
{
    /**
     * @var mixed|string
     */
    private string $name;

    public function __construct(string $name = 'posts')
    {
        $this->name = $name;
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $args[$this->name] = $request->getParsedBody();
        return $args;
    }
}