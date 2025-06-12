<?php

return [
    /**
     * Available languages
     */

    'locale' => 'de',
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
     * Cookie for column settings
     */

    'cookie_settings_days' => 90,

    /**
     * Modules base_path
     */
    'modules_base_path' => base_path('modules'),

    /**
     * Translate scan configuration
     */
    'scan_directories' => [
        'Resources/Views',
        'Http',
        'Services',
        'Repositories',
    ],

];
