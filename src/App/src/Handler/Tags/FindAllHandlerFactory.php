<?php

declare(strict_types=1);

namespace App\Handler\Tags;

use App\Model\TagModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindAllHandler($container->get(TagModel::class));
    }
}
