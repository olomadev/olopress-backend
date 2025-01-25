<?php

declare(strict_types=1);

namespace App\Handler\Screenshots;

use App\Model\ScreenshotModel;
use Olobase\Mezzio\DataManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindOneByIdHandler($container->get(ScreenshotModel::class));
    }
}
