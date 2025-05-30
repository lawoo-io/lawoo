<?php

return [
    'web.apps' => [
        'name' => 'Applications',
        'route' => 'web.apps.index',
        'middleware' => 'permission:web.apps.view',
        'level' => 0,
        'icon' => '',
        'sort_order' => 10000,
        'group' => null,
    ],

    'web.apps.list' => [
        'name' => 'Applications List',
        'route' => 'web.apps.list',
        'parent' => 'web.apps',
        'middleware' => 'permission:web.apps.view',
        'level' => 1,
        'icon' => '',
        'sort_order' => 100,
        'group' => null,
    ]
];
