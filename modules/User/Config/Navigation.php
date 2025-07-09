<?php

return [
    # Level 0

    'user.users' => [
        'name' => 'Users',
        'route' => 'lawoo.users.records',
        'middleware' => 'user.users.show',
        'level' => 0,
        'icon' => 'users',
        'sort_order' => 9900,
        'group' => null,
    ],

    # Level 1
    'user.users.lists' => [
        'parent' => 'user.users',
        'name' => 'User Lists',
        'route' => 'lawoo.users.records',
        'middleware' => 'user.users.show',
        'level' => 1,
        'icon' => null,
        'sort_order' => 100,
        'group' => null,
    ],
];
