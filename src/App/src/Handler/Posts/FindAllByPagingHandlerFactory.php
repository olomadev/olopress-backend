<?php

declare(strict_types=1);

namespace App\Handler\Posts;

use App\Model\PostModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindAllByPagingHandler($container->get(PostModel::class));
    }
}
