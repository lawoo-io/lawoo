<?php

return [
    'website.pages' => [
        'name' => 'Websites',
        'route' => 'lawoo.website.pages.records',
        'middleware' => 'website.pages.view',
        'level' => 0,
        'icon' => 'globe-europe-africa',
        'sort_order' => 9800,
        'group' => null
    ],
    'website.website' => [
        'parent' => 'website.pages',
        'name' => 'Websites',
        'route' => 'lawoo.website.records',
        'middleware' => 'website.website.view',
        'level' => 1,
        'icon' => null,
        'sort_order' => 100,
        'group' => null
    ],
    'website.pages.records' => [
        'parent' => 'website.pages',
        'name' => 'Pages',
        'route' => 'lawoo.website.pages.records',
        'middleware' => 'website.pages.view',
        'level' => 1,
        'icon' => null,
        'sort_order' => 200,
        'group' => null
    ],
    'website.layouts.records' => [
        'parent' => 'website.pages',
        'name' => 'Layouts',
        'route' => 'lawoo.website.layouts.records',
        'middleware' => 'website.layouts.view',
        'level' => 1,
        'icon' => null,
        'sort_order' => 300,
        'group' => null
    ],
    'website.assets.records' => [
        'parent' => 'website.pages',
        'name' => 'Assets',
        'route' => 'lawoo.website.assets.records',
        'middleware' => 'website.assets.view',
        'level' => 1,
        'icon' => null,
        'sort_order' => 400,
        'group' => null
    ],
    'website.themes.records' => [
        'parent' => 'website.pages',
        'name' => 'Themes',
        'route' => 'lawoo.website.themes.records',
        'middleware' => 'website.themes.view',
        'level' => 1,
        'icon' => null,
        'sort_order' => 500,
        'group' => null
    ]
];
