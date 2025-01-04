<?php

declare(strict_types=1);

namespace App\Handler\Files;

use App\Model\FileModel;
use App\Filter\Files\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class DeleteHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $fileModel = $container->get(FileModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(DeleteFilter::class);

        return new DeleteHandler($fileModel, $inputFilter, $error);
    }
}
