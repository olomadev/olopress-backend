<?php

declare(strict_types=1);

namespace App\Handler\Posts;

use App\Model\PostModel;
use Olobase\Mezzio\DataManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $postModel = $container->get(PostModel::class);
        $dataManager = $container->get(DataManagerInterface::class);
        return new FindOneByIdHandler($postModel, $dataManager);
    }
}
