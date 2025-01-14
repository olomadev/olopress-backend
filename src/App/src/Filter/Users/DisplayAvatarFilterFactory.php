<?php

declare(strict_types=1);

namespace App\Filter\Users;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DisplayAvatarFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DisplayAvatarFilter;
    }
}
