<?php

use Modules\Web\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('settings')) {
    function settings(string $key)
    {
        return Cache::tags(['table:settings'])->remember("settings.{$key}", now()->addDays(config('app.settings_cache_duration')), function () use ($key) {
            return Setting::getByKey($key);
        });
    }
}
