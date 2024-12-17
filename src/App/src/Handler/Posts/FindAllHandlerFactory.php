<?php

declare(strict_types=1);

namespace App\Handler\Posts;

use App\Model\PostModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindAllHandler($container->get(PostModel::class));
    }
}
