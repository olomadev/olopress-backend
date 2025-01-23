<?php

declare(strict_types=1);

namespace App;

use Olobase\Mezzio\ColumnFiltersInterface;
use Olobase\Mezzio\Authentication\JwtEncoderInterface;
use Olobase\Mezzio\Authorization\PermissionModelInterface;

use Predis\ClientInterface as PredisInterface;
use Laminas\Cache\Storage\StorageInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\EventManager\EventManagerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'input_filters' => [
                'factories' => [
                    // Core Input Filters
                    Filter\ObjectInputFilter::class => Container\ObjectInputFilterFactory::class,
                    Filter\CollectionInputFilter::class => Container\CollectionInputFilterFactory::class,
                    // Auth
                    Filter\Auth\AuthFilter::class => InvokableFactory::class,
                    Filter\Auth\ResetPasswordFilter::class => Filter\Auth\ResetPasswordFilterFactory::class,
                    Filter\Auth\ChangePasswordFilter::class => Filter\Auth\ChangePasswordFilterFactory::class,
                    // Account
                    Filter\Account\SaveFilter::class => Filter\Account\SaveFilterFactory::class,
                    Filter\Account\PasswordChangeFilter::class => Filter\Account\PasswordChangeFilterFactory::class,
                    // Categories
                    Filter\Categories\SaveFilter::class => Filter\Categories\SaveFilterFactory::class,
                    Filter\Categories\DeleteFilter::class => Filter\Categories\DeleteFilterFactory::class,
                    // Files
                    Filter\Files\SaveFilter::class => Filter\Files\SaveFilterFactory::class,
                    Filter\Files\DeleteFilter::class => Filter\Files\DeleteFilterFactory::class,
                    Filter\Files\DisplayFilter::class => Filter\Files\DisplayFilterFactory::class,
                    // Failed Logins
                    Filter\FailedLogins\SaveFilter::class => Filter\FailedLogins\SaveFilterFactory::class,
                    Filter\FailedLogins\DeleteFilter::class => Filter\FailedLogins\DeleteFilterFactory::class,
                    // Permissions
                    Filter\Permissions\SaveFilter::class => Filter\Permissions\SaveFilterFactory::class,
                    Filter\Permissions\DeleteFilter::class => Filter\Permissions\DeleteFilterFactory::class,
                    // Pages
                    Filter\Pages\SaveFilter::class => Filter\Pages\SaveFilterFactory::class,
                    Filter\Pages\PublishFilter::class => Filter\Pages\PublishFilterFactory::class,
                    Filter\Pages\DeleteFilter::class => Filter\Pages\DeleteFilterFactory::class,
                    // Posts
                    Filter\Posts\SaveFilter::class => Filter\Posts\SaveFilterFactory::class,
                    Filter\Posts\PublishFilter::class => Filter\Posts\PublishFilterFactory::class,
                    Filter\Posts\DeleteFilter::class => Filter\Posts\DeleteFilterFactory::class,
                    // Roles
                    Filter\Roles\SaveFilter::class => Filter\Roles\SaveFilterFactory::class,
                    Filter\Roles\DeleteFilter::class => Filter\Roles\DeleteFilterFactory::class,
                    // Tags
                    Filter\Tags\SaveFilter::class => Filter\Tags\SaveFilterFactory::class,
                    Filter\Tags\DeleteFilter::class => Filter\Tags\DeleteFilterFactory::class,
                    // Users
                    Filter\Users\SaveFilter::class => Filter\Users\SaveFilterFactory::class,
                    Filter\Users\DeleteFilter::class => Filter\Users\DeleteFilterFactory::class,
                    Filter\Users\PasswordSaveFilter::class => Filter\Users\PasswordSaveFilterFactory::class,
                    Filter\Users\DisplayAvatarFilter::class => Filter\Users\DisplayAvatarFilterFactory::class,
                ],
            ],
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'invokables' => [
                // Handler\PingHandler::class => Handler\PingHandler::class,
            ],
            'delegators' => [
                TranslatorInterface::class => [
                    'App\Container\TranslatorDelegatorFactory',
                ],
            ],
            'aliases' => [
                \Mezzio\Authentication\AuthenticationInterface::class => Authentication\JwtAuthentication::class,
                \App\Model\PermissionModel::class => PermissionModelInterface::class,
            ],
            'factories' => [
                // Classes
                //
                Authentication\JwtAuthentication::class => Authentication\JwtAuthenticationFactory::class,
                Middleware\ClientMiddleware::class => Middleware\ClientMiddlewareFactory::class,
                Middleware\RestrictedModeMiddleware::class => Middleware\RestrictedModeMiddlewareFactory::class,
                Middleware\JwtAuthenticationMiddleware::class => Middleware\JwtAuthenticationMiddlewareFactory::class,
                Listener\LoginListener::class => Listener\LoginListenerFactory::class,
                Utils\ErrorMailer::class => Utils\ErrorMailerFactory::class,
                Utils\SmtpMailer::class => Utils\SmtpMailerFactory::class,
                Utils\TokenEncrypt::class => Utils\TokenEncryptFactory::class,
                StorageInterface::class => Container\CacheFactory::class,
                SimpleCacheInterface::class => Container\SimpleCacheFactory::class,   
                PredisInterface::class => Container\PredisFactory::class,
                EventManagerInterface::class => Container\EventManagerFactory::class,

                // Handlers
                //------------------------------------------
                // common
                Handler\Common\Actions\FindAllHandler::class => Handler\Common\Actions\FindAllHandlerFactory::class,
                Handler\Common\Methods\FindAllHandler::class => Handler\Common\Methods\FindAllHandlerFactory::class,
                Handler\Common\Locales\FindAllHandler::class => Handler\Common\Locales\FindAllHandlerFactory::class,
                Handler\Common\Cities\FindAllHandler::class => Handler\Common\Cities\FindAllHandlerFactory::class,
                Handler\Common\Countries\FindAllHandler::class => Handler\Common\Countries\FindAllHandlerFactory::class,
                Handler\Common\Currencies\FindAllHandler::class => Handler\Common\Currencies\FindAllHandlerFactory::class,
                Handler\Common\AreaCodes\FindAllHandler::class => Handler\Common\AreaCodes\FindAllHandlerFactory::class,

                // auth
                Handler\Auth\TokenHandler::class => Handler\Auth\TokenHandlerFactory::class,
                Handler\Auth\RefreshHandler::class => Handler\Auth\RefreshHandlerFactory::class,
                Handler\Auth\LogoutHandler::class => Handler\Auth\LogoutHandlerFactory::class,
                Handler\Auth\SessionUpdateHandler::class => Handler\Auth\SessionUpdateHandlerFactory::class,
                Handler\Auth\ResetPasswordHandler::class => Handler\Auth\ResetPasswordHandlerFactory::class,
                Handler\Auth\CheckResetCodeHandler::class => Handler\Auth\CheckResetCodeHandlerFactory::class,
                Handler\Auth\ChangePasswordHandler::class => Handler\Auth\ChangePasswordHandlerFactory::class,
                // account
                Handler\Account\FindMeHandler::class => Handler\Account\FindMeHandlerFactory::class,
                Handler\Account\UpdateHandler::class => Handler\Account\UpdateHandlerFactory::class,
                Handler\Account\UpdatePasswordHandler::class => Handler\Account\UpdatePasswordHandlerFactory::class,
                // categories
                Handler\Categories\CreateHandler::class => Handler\Categories\CreateHandlerFactory::class,
                Handler\Categories\UpdateHandler::class => Handler\Categories\UpdateHandlerFactory::class,
                Handler\Categories\DeleteHandler::class => Handler\Categories\DeleteHandlerFactory::class,
                Handler\Categories\FindAllHandler::class => Handler\Categories\FindAllHandlerFactory::class,
                Handler\Categories\FindAllByPagingHandler::class => Handler\Categories\FindAllByPagingHandlerFactory::class,
                // files
                Handler\Files\CreateHandler::class => Handler\Files\CreateHandlerFactory::class,
                Handler\Files\DeleteHandler::class => Handler\Files\DeleteHandlerFactory::class,
                Handler\Files\FindByNameHandler::class => Handler\Files\FindByNameHandlerFactory::class,
                // featured-images
                Handler\FeaturedImages\FindAllHandler::class => Handler\FeaturedImages\FindAllHandlerFactory::class,
                // pages
                Handler\Pages\CreateHandler::class => Handler\Pages\CreateHandlerFactory::class,
                Handler\Pages\UpdateHandler::class => Handler\Pages\UpdateHandlerFactory::class,
                Handler\Pages\PublishHandler::class => Handler\Pages\PublishHandlerFactory::class,
                Handler\Pages\DeleteHandler::class => Handler\Pages\DeleteHandlerFactory::class,
                Handler\Pages\FindOneByIdHandler::class => Handler\Pages\FindOneByIdHandlerFactory::class,
                Handler\Pages\FindAllHandler::class => Handler\Pages\FindAllHandlerFactory::class,
                Handler\Pages\FindAllByPagingHandler::class => Handler\Pages\FindAllByPagingHandlerFactory::class,
                // posts
                Handler\Posts\CreateHandler::class => Handler\Posts\CreateHandlerFactory::class,
                Handler\Posts\UpdateHandler::class => Handler\Posts\UpdateHandlerFactory::class,
                Handler\Posts\PublishHandler::class => Handler\Posts\PublishHandlerFactory::class,
                Handler\Posts\DeleteHandler::class => Handler\Posts\DeleteHandlerFactory::class,
                Handler\Posts\FindOneByIdHandler::class => Handler\Posts\FindOneByIdHandlerFactory::class,
                Handler\Posts\FindAllHandler::class => Handler\Posts\FindAllHandlerFactory::class,
                Handler\Posts\FindAllByPagingHandler::class => Handler\Posts\FindAllByPagingHandlerFactory::class,
                // users
                Handler\Users\CreateHandler::class => Handler\Users\CreateHandlerFactory::class,
                Handler\Users\UpdateHandler::class => Handler\Users\UpdateHandlerFactory::class,
                Handler\Users\DeleteHandler::class => Handler\Users\DeleteHandlerFactory::class,
                Handler\Users\FindOneByIdHandler::class => Handler\Users\FindOneByIdHandlerFactory::class,
                Handler\Users\FindAllHandler::class => Handler\Users\FindAllHandlerFactory::class,
                Handler\Users\FindAllByPagingHandler::class => Handler\Users\FindAllByPagingHandlerFactory::class,
                Handler\Users\DisplayAvatarByIdHandler::class => Handler\Users\DisplayAvatarByIdHandlerFactory::class,
                // roles
                Handler\Roles\CreateHandler::class => Handler\Roles\CreateHandlerFactory::class,
                Handler\Roles\UpdateHandler::class => Handler\Roles\UpdateHandlerFactory::class,
                Handler\Roles\DeleteHandler::class => Handler\Roles\DeleteHandlerFactory::class,
                Handler\Roles\FindOneByIdHandler::class => Handler\Roles\FindOneByIdHandlerFactory::class,
                Handler\Roles\FindAllHandler::class => Handler\Roles\FindAllHandlerFactory::class,
                Handler\Roles\FindAllByPagingHandler::class => Handler\Roles\FindAllByPagingHandlerFactory::class,
                // permissions
                Handler\Permissions\CopyHandler::class => Handler\Permissions\CopyHandlerFactory::class,
                Handler\Permissions\CreateHandler::class => Handler\Permissions\CreateHandlerFactory::class,
                Handler\Permissions\UpdateHandler::class => Handler\Permissions\UpdateHandlerFactory::class,
                Handler\Permissions\DeleteHandler::class => Handler\Permissions\DeleteHandlerFactory::class,
                Handler\Permissions\FindAllHandler::class => Handler\Permissions\FindAllHandlerFactory::class,
                Handler\Permissions\FindAllByPagingHandler::class => Handler\Permissions\FindAllByPagingHandlerFactory::class,
                // tags
                Handler\Tags\CreateHandler::class => Handler\Tags\CreateHandlerFactory::class,
                Handler\Tags\UpdateHandler::class => Handler\Tags\UpdateHandlerFactory::class,
                Handler\Tags\DeleteHandler::class => Handler\Tags\DeleteHandlerFactory::class,
                Handler\Tags\FindAllHandler::class => Handler\Tags\FindAllHandlerFactory::class,
                Handler\Tags\FindAllByPagingHandler::class => Handler\Tags\FindAllByPagingHandlerFactory::class,
                // failed logins
                Handler\FailedLogins\DeleteHandler::class => Handler\FailedLogins\DeleteHandlerFactory::class,
                Handler\FailedLogins\FindAllByPagingHandler::class => Handler\FailedLogins\FindAllByPagingHandlerFactory::class,
                Handler\FailedLogins\FindAllIpAdressesHandler::class => Handler\FailedLogins\FindAllIpAdressesHandlerFactory::class,
                Handler\FailedLogins\FindAllUsernamesHandler::class => Handler\FailedLogins\FindAllUsernamesHandlerFactory::class,
                
                // Models
                //
                Model\AuthModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new Model\AuthModel($dbAdapter);
                },
                Model\CategoryModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $categories = new TableGateway('categories', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    return new Model\CategoryModel($categories, $cacheStorage);
                },
                Model\CommonModel::class => function ($container) {
                    $config = $container->get('config');
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    return new Model\CommonModel($dbAdapter, $cacheStorage, $config);
                },
                Model\FailedLoginModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $simpleCache = $container->get(SimpleCacheInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $failedLogins = new TableGateway('failedLogins', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\FailedLoginModel($users, $failedLogins, $simpleCache, $columnFilters);
                },
                Model\FileModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $files = new TableGateway('files', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $postFiles = new TableGateway('postFiles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $pageFiles = new TableGateway('pageFiles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $posts = new TableGateway('posts', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\FileModel($files, $postFiles, $pageFiles, $posts);
                },
                PermissionModelInterface::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $permissions = new TableGateway('permissions', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\PermissionModel(
                        $permissions, 
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\PageModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $pages = new TableGateway('pages', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $files = new TableGateway('files', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\PageModel(
                        $pages,
                        $files,
                        $cacheStorage,
                        $columnFilters
                    );
                },   
                Model\PostModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $predisClient = $container->get(PredisInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $tags = new TableGateway('tags', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $files = new TableGateway('files', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $posts = new TableGateway('posts', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $postsTags = new TableGateway('postTags', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $postsCategories = new TableGateway('postCategories', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\PostModel(
                        $posts,
                        $postsTags,
                        $postsCategories,
                        $tags,
                        $files,
                        $cacheStorage,
                        $predisClient,
                        $columnFilters
                    );
                },                
                Model\RoleModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $roles = new TableGateway('roles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $rolePermissions = new TableGateway('rolePermissions', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\RoleModel(
                        $roles,
                        $rolePermissions,
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\TagModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $tags = new TableGateway('tags', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\TagModel($tags, $columnFilters);
                },
                Model\TokenModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $tokenEncrypt = $container->get(Utils\TokenEncrypt::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $jwtEncoder = $container->get(JwtEncoderInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null);
                    return new Model\TokenModel($container->get('config'), $cacheStorage, $tokenEncrypt, $jwtEncoder, $users);
                },
                Model\UserModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $userRoles = new TableGateway('userRoles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $userAvatars = new TableGateway('userAvatars', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\UserModel(
                        $users,
                        $userRoles,
                        $userAvatars,
                        $cacheStorage,
                        $columnFilters
                    );
                },

            ]
        ];
    }
}