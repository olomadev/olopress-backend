<?php

declare(strict_types=1);

namespace App\Handler\Comments;

use App\Model\CommentModel;
use App\Filter\Comments\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class DeleteHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $error = $container->get(Error::class);
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(DeleteFilter::class);

        return new DeleteHandler($container->get(CommentModel::class), $inputFilter, $error);
    }
}
