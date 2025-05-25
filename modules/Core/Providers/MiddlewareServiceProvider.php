<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Modules\Core\Http\Middleware\HasRole;
use Modules\Core\Http\Middleware\HasPermission;
use Modules\Core\Http\Middleware\HasRoleOrPermission;
use Modules\Core\Http\Middleware\CheckActiveUser;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);

        // Register RBAC middleware
        $router->aliasMiddleware('role', HasRole::class);
        $router->aliasMiddleware('permission', HasPermission::class);
        $router->aliasMiddleware('role.or.permission', HasRoleOrPermission::class);
        $router->aliasMiddleware('active.user', CheckActiveUser::class);

        // Add to middleware groups
        $router->pushMiddlewareToGroup('web', CheckActiveUser::class);
    }
}
