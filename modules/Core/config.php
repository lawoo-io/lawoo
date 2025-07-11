<?php

return [
    /**
     * Available languages
     */

    'locale' => 'en',
    'fallback_locale' => 'en',

    'locales' => [
        'en' => 'English',
        'de' => 'Deutsch',
    ],

    'date_formats' => [
        'en' => 'm/d/Y',
        'de' => 'd.m.Y',
    ],

    /**
     * Settings cache duration in days
     */
    'settings_cache_duration' => 90,

    /**
     * Cookie for column settings
     */

    'cookie_settings_days' => 90,

    /**
     * Cache duration by List, Kanban
     * minutes
     */

    'cache_duration' => 120,

    /**
     * Modules base_path
     */
    'modules_base_path' => base_path('modules'),

    /**
     * Module Paths
     */
    'modules_base_paths' => [
        base_path('vendor/lawoo-io/lawoo/modules'),
        base_path('modules'),
    ],

    /**
     * Translate scan configuration
     */
    'scan_directories' => [
        'Data',
        'Models',
        'Resources/Views',
        'Http',
        'Services',
        'Repositories',
    ],

];
