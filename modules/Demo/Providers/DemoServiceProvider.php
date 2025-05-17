<?php

namespace Modules\Demo\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Models\ModuleCategory;
use Modules\Core\Services\Modules\ModuleOverrider;
use Modules\Demo\Models\ExtendCore;

class DemoServiceProvider extends ServiceProvider
{

    public function register(): void
    {
//        $this->app->bind(ModuleCategory::class, ExtendCore::class);
    }

    public function boot(): void {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
