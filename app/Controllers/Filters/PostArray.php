<?php
declare(strict_types=1);

namespace App\Controllers\Filters;

use App\Application\Interfaces\ControllerArgFilterInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostArray implements ControllerArgFilterInterface
{
    private $name = 'posts';

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $args[$this->name] = $request->getParsedBody();

        return $args;
    }

    /**
     * @param string $name
     * @return PostArray
     */
    public function setName(string $name): PostArray
    {
        $this->name = $name;
        return $this;
    }
}