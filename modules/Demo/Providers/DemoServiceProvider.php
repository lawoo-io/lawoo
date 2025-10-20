<?php

namespace Modules\Demo\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Core\Services\Modules\ModuleOverrider;
use Modules\Demo\Http\Livewire\Counter;

class DemoServiceProvider extends ServiceProvider
{

    public function register(): void
    {
//        $this->app->bind(ModuleCategory::class, ExtendCore::class);
    }

    public function boot(): void {
//        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'demo');
//        Blade::anonymousComponentPath( __DIR__ . '/../Resources/Views/components');

        /**
         * Register Livewire Components
         */
        Livewire::component('web.counter', Counter::class);
    }
}
