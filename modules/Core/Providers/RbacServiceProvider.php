<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;
use Modules\Core\Services\PermissionRegistrar;
use Modules\Core\Contracts\PermissionRegistrarInterface;

class RbacServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Permission Registrar
        $this->app->singleton(PermissionRegistrarInterface::class, PermissionRegistrar::class);
        $this->app->singleton('permission.registrar', PermissionRegistrarInterface::class);

        // Bind Extended User Model
//        $this->app->bind(
//            \App\Models\User::class,
//            \Modules\Core\Models\ExtendedUser::class
//        );

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Gates for Permissions
        $this->registerGates();

        // Auto-discover and register module permissions
        $this->discoverModulePermissions();

        // Register cache clear commands
        $this->registerCommands();

//        $this->app->extend(\App\Models\User::class, function ($app) {
//            return new \Modules\Core\Models\UserExtended();
//        });
        config(['auth.providers.users.model' => \Modules\Core\Models\UserExtended::class]);
//        $user = $this->app->make(\App\Models\User::class);
//        $user = \App\Models\User::find(1);
//        dd(get_class($user));
    }

    /**
     * Register permission gates dynamically
     */
    protected function registerGates(): void
    {
        Gate::before(function ($user, $ability) {
            // Super admin bypass
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }

            // Check if user is active
            if (method_exists($user, 'is_active') && !$user->is_active) {
                return false;
            }

            return null; // Continue with normal gate checks
        });

        // Register all permissions as gates
        if ($this->app->runningInConsole() === false) {
            $this->registerPermissionGates();
        }
    }

    /**
     * Register all permissions as Laravel gates
     */
    protected function registerPermissionGates(): void
    {
        $permissions = Cache::remember('rbac.permissions', 3600, function () {
            return Permission::all(['slug', 'name']);
        });

        foreach ($permissions as $permission) {
            Gate::define($permission->slug, function ($user) use ($permission) {
                return $user->hasPermissionViaRole($permission->slug);
            });
        }
    }

    /**
     * Auto-discover permissions from modules
     */
    protected function discoverModulePermissions(): void
    {
        if ($this->app->runningInConsole()) {
            return; // Skip during artisan commands to avoid DB issues
        }

        $registrar = $this->app->make(PermissionRegistrarInterface::class);

        // Get all module directories
        $modulesPath = base_path('modules');

        if (!is_dir($modulesPath)) {
            return;
        }

        $modules = array_filter(scandir($modulesPath), function ($item) use ($modulesPath) {
            return $item !== '.' && $item !== '..' && is_dir($modulesPath . '/' . $item);
        });

        foreach ($modules as $module) {
            $this->registerModulePermissions($module, $registrar);
        }
    }

    /**
     * Register permissions for a specific module
     */
    protected function registerModulePermissions(string $module, PermissionRegistrarInterface $registrar): void
    {
        // Look for permissions.php config file in module
        $permissionsFile = base_path("modules/{$module}/Config/permissions.php");

        if (file_exists($permissionsFile)) {
            $permissions = require $permissionsFile;
            $registrar->registerPermissions($module, $permissions);
        }

        // Look for PermissionProvider class in module
        $providerClass = "Modules\\{$module}\\Providers\\PermissionProvider";

        if (class_exists($providerClass)) {
            $provider = new $providerClass();
            if (method_exists($provider, 'getPermissions')) {
                $permissions = $provider->getPermissions();
                $registrar->registerPermissions($module, $permissions);
            }
        }
    }

    /**
     * Register artisan commands
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Core\Console\Commands\ClearPermissionCache::class,
                \Modules\Core\Console\Commands\SyncModulePermissions::class,
            ]);
        }
    }
}
