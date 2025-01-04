<?php

declare(strict_types=1);

namespace App\Filter\Files;

use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DisplayFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DisplayFilter($container->get(AdapterInterface::class));
    }
}
