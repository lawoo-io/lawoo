<?php

namespace Modules\Web\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Web\Extends\Traits\ExtendUser;
use Modules\Web\Extends\Models\ExtendUserModel;

class WebServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        // same code
    }

    public function boot(): void
    {
        /**
         * Register RouteServiceProvider
         */
        $this->app->register(RouteServiceProvider::class);

    }
}
