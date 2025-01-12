<?php

declare(strict_types=1);

namespace App\Handler\Pages;

use App\Model\PageModel;
use Olobase\Mezzio\DataManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pageModel = $container->get(PageModel::class);
        $dataManager = $container->get(DataManagerInterface::class);
        return new FindOneByIdHandler($pageModel, $dataManager);
    }
}
