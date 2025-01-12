<?php

declare(strict_types=1);

namespace App\Handler\Pages;

use App\Model\PageModel;
use App\Filter\Pages\PublishFilter;
use Olobase\Mezzio\DataManagerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class PublishHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pageModel = $container->get(PageModel::class);
        $error = $container->get(Error::class);
        $dataManager = $container->get(DataManagerInterface::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(PublishFilter::class);

        return new PublishHandler($pageModel, $dataManager, $inputFilter, $error);
    }
}
