<?php

declare(strict_types=1);

namespace App\Handler\Files;

use App\Model\FileModel;
use App\Filter\Files\DisplayFilter;
use Psr\Container\ContainerInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class FindByNameHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $translator = $container->get(Translator::class);
        $fileModel = $container->get(FileModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(DisplayFilter::class);

        return new FindByNameHandler($translator, $fileModel, $inputFilter, $error);
    }
}
