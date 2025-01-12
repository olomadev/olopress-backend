<?php

declare(strict_types=1);

namespace App\Filter\Pages;

use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PublishFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PublishFilter(
            $container->get(AdapterInterface::class),
            $container->get(InputFilterPluginManager::class)
        );
    }
}
