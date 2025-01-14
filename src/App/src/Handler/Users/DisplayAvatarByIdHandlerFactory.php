<?php

declare(strict_types=1);

namespace App\Handler\Users;

use App\Model\UserModel;
use App\Filter\Users\DisplayAvatarFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class DisplayAvatarByIdHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $userModel = $container->get(UserModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(DisplayAvatarFilter::class);

        return new DisplayAvatarByIdHandler($userModel, $inputFilter, $error);
    }
}
