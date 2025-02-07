<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use App\Middleware\JwtAuthenticationMiddleware;
use Psr\Container\ContainerInterface;
/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/:id', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 * 
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Mezzio\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */
return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {

    // Auth (public)
    $app->route('/api/auth/token', App\Handler\Auth\TokenHandler::class, ['POST']);
    $app->route('/api/auth/refresh', [App\Handler\Auth\RefreshHandler::class], ['POST']);
    $app->route('/api/auth/logout', [App\Handler\Auth\LogoutHandler::class], ['GET']);
    $app->route('/api/auth/session', [JwtAuthenticationMiddleware::class, App\Handler\Auth\SessionUpdateHandler::class], ['POST']);
    $app->route('/api/auth/resetPassword', [App\Handler\Auth\ResetPasswordHandler::class], ['POST']);
    $app->route('/api/auth/checkResetCode', [App\Handler\Auth\CheckResetCodeHandler::class], ['GET']);
    $app->route('/api/auth/changePassword', [App\Handler\Auth\ChangePasswordHandler::class], ['POST']);
    
    $auth = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
    ];
    // Account (private)
    $app->route('/api/account/findMe', [...$auth, ...[App\Handler\Account\FindMeHandler::class]], ['GET']);
    $app->route('/api/account/update', [...$auth, ...[App\Handler\Account\UpdateHandler::class]], ['PUT']);
    $app->route('/api/account/updatePassword', [...$auth, ...[App\Handler\Account\UpdatePasswordHandler::class]], ['PUT']);

    // Categories
    $app->route('/api/categories/findAll', [...$auth, ...[App\Handler\Categories\FindAllHandler::class]], ['GET']);
    $app->route('/api/categories/findAllByPaging', [...$auth, ...[App\Handler\Categories\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/categories/create', [...$auth, ...[App\Handler\Categories\CreateHandler::class]], ['POST']);
    $app->route('/api/categories/delete/:categoryId', [...$auth, ...[App\Handler\Categories\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/categories/update/:categoryId', [...$auth, ...[App\Handler\Categories\UpdateHandler::class]], ['PUT']);

    // Comments (private)
    $app->route('/api/comments/delete/:commentId', [...$auth, ...[App\Handler\Comments\DeleteHandler::class]], ['DELETE']);;
    $app->route('/api/comments/findAllByPaging', [...$auth, ...[App\Handler\Comments\FindAllByPagingHandler::class]], ['GET']);

    // Featured images
    $app->route('/api/featured-images/findAll', [...$auth, ...[App\Handler\FeaturedImages\FindAllHandler::class]], ['GET']);

    // Files
    $app->route('/api/files/display', App\Handler\Files\FindByNameHandler::class, ['GET']);
    $app->route('/api/files/create', [...$auth, ...[App\Handler\Files\CreateHandler::class]], ['POST']);
    $app->route('/api/files/delete', [...$auth, ...[App\Handler\Files\DeleteHandler::class]], ['DELETE']);

    // Posts (private)
    $app->route('/api/posts/create', [...$auth, ...[App\Handler\Posts\CreateHandler::class]], ['POST']);
    $app->route('/api/posts/update/:postId', [...$auth, ...[App\Handler\Posts\UpdateHandler::class]], ['PUT']);
    $app->route('/api/posts/publish/:postId', [...$auth, ...[App\Handler\Posts\PublishHandler::class]], ['PATCH']);
    $app->route('/api/posts/delete/:postId', [...$auth, ...[App\Handler\Posts\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/posts/findAll', [App\Handler\Posts\FindAllHandler::class], ['GET']);
    $app->route('/api/posts/findAllByPaging', [...$auth, ...[App\Handler\Posts\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/posts/findOneById/:postId', [...$auth, ...[App\Handler\Posts\FindOneByIdHandler::class]], ['GET']);

    // Pages (private)
    $app->route('/api/pages/create', [...$auth, ...[App\Handler\Pages\CreateHandler::class]], ['POST']);
    $app->route('/api/pages/update/:pageId', [...$auth, ...[App\Handler\Pages\UpdateHandler::class]], ['PUT']);
    $app->route('/api/pages/publish/:pageId', [...$auth, ...[App\Handler\Pages\PublishHandler::class]], ['PATCH']);
    $app->route('/api/pages/delete/:pageId', [...$auth, ...[App\Handler\Pages\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/pages/findAll', [App\Handler\Pages\FindAllHandler::class], ['GET']);
    $app->route('/api/pages/findAllByPaging', [...$auth, ...[App\Handler\Pages\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/pages/findOneById/:pageId', [...$auth, ...[App\Handler\Pages\FindOneByIdHandler::class]], ['GET']);

    // Roles (private)
    $app->route('/api/roles/create', [...$auth, ...[App\Handler\Roles\CreateHandler::class]], ['POST']);
    $app->route('/api/roles/update/:roleId', [...$auth, ...[App\Handler\Roles\UpdateHandler::class]], ['PUT']);
    $app->route('/api/roles/delete/:roleId', [...$auth, ...[App\Handler\Roles\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/roles/findAll', [App\Handler\Roles\FindAllHandler::class], ['GET']);
    $app->route('/api/roles/findAllByPaging', [...$auth, ...[App\Handler\Roles\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/roles/findOneById/:roleId', [...$auth, ...[App\Handler\Roles\FindOneByIdHandler::class]], ['GET']);

    // Users (private)
    $app->route('/api/users/create', [...$auth, [App\Handler\Users\CreateHandler::class]], ['POST']);
    $app->route('/api/users/update/:userId', [...$auth, [App\Handler\Users\UpdateHandler::class]], ['PUT']);
    $app->route('/api/users/delete/:userId', [...$auth, [App\Handler\Users\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/users/updatePassword/:userId', [...$auth, [App\Handler\Users\UpdatePasswordHandler::class]], ['PUT']);
    $app->route('/api/users/findAll', [...$auth, [App\Handler\Users\FindAllHandler::class]], ['GET']);
    $app->route('/api/users/findAllByPaging', [...$auth, [App\Handler\Users\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/users/findOneById/:userId', [...$auth, [App\Handler\Users\FindOneByIdHandler::class]], ['GET']);
    
    // Users (public)
    $app->route('/api/users/displayAvatar/:userId', App\Handler\Users\DisplayAvatarByIdHandler::class, ['GET']);

    // Avatars (private)
    $app->route('/api/avatars/findOneById/:userId', [...$auth, [App\Handler\Users\FindOneByIdHandler::class]], ['GET']);
    $app->route('/api/avatars/update/:userId', [...$auth, [App\Handler\Users\UpdateHandler::class]], ['PUT']);

    // Permissions (private)
    $app->route('/api/permissions/create', [...$auth, [App\Handler\Permissions\CreateHandler::class]], ['POST']);
    $app->route('/api/permissions/copy/:permId', [...$auth, [App\Handler\Permissions\CopyHandler::class]], ['POST']);
    $app->route('/api/permissions/update/:permId', [...$auth, [App\Handler\Permissions\UpdateHandler::class]], ['PUT']);
    $app->route('/api/permissions/delete/:permId', [...$auth, [App\Handler\Permissions\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/permissions/findAll', [JwtAuthenticationMiddleware::class, App\Handler\Permissions\FindAllHandler::class], ['GET']);
    $app->route('/api/permissions/findAllByPaging', [...$auth, [App\Handler\Permissions\FindAllByPagingHandler::class]], ['GET']);

    // Screenshots (public)
    $app->route('/api/screenshots/display/:id', App\Handler\Screenshots\FindOneByIdHandler::class, ['GET']);

    // Tags
    $app->route('/api/tags/findAll', [...$auth, ...[App\Handler\Tags\FindAllHandler::class]], ['GET']);
    $app->route('/api/tags/findAllByPaging', [...$auth, ...[App\Handler\Tags\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/tags/create', [...$auth, ...[App\Handler\Tags\CreateHandler::class]], ['POST']);
    $app->route('/api/tags/update/:tagId', [...$auth, ...[App\Handler\Tags\UpdateHandler::class]], ['PUT']);

    // FailedLogins (private)
    $app->route('/api/failedlogins/delete/:loginId', [...$auth, [App\Handler\FailedLogins\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/failedlogins/findAllByPaging', [...$auth, [App\Handler\FailedLogins\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/failedloginips/findAll', [...$auth, [App\Handler\FailedLogins\FindAllIpAdressesHandler::class]], ['GET']);
    $app->route('/api/failedloginusernames/findAll', [...$auth, [App\Handler\FailedLogins\FindAllUsernamesHandler::class]], ['GET']);
    
    // Common (public)
    // 
    $app->route('/api/actions/findAll', App\Handler\Common\Actions\FindAllHandler::class, ['GET']);
    $app->route('/api/methods/findAll', App\Handler\Common\Methods\FindAllHandler::class, ['GET']);
    $app->route('/api/stream/events', App\Handler\Common\Stream\EventsHandler::class, ['GET']);
    $app->route('/api/locales/findAll', App\Handler\Common\Locales\FindAllHandler::class, ['GET']);
    $app->route('/api/months/findAll', App\Handler\Common\Months\FindAllHandler::class, ['GET']);
    $app->route('/api/countries/findAll', App\Handler\Common\Countries\FindAllHandler::class, ['GET']);

};
