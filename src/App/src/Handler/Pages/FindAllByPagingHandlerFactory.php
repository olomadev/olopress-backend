<?php

declare(strict_types=1);

namespace App\Handler\Pages;

use App\Model\PageModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindAllByPagingHandler($container->get(PageModel::class));
    }
}
