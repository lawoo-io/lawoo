<?php

namespace Modules\Website\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as BaseRouteServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends BaseRouteServiceProvider
{
    public function boot(): void {
        $this->routes(function () {
            Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
            Route::middleware('web')->group(__DIR__ . '/../Routes/website.php');
        });

    }
}
