<?php

return [
    'contact' => [
        'name' => 'Contact',
        'route' => 'lawoo.contact.list',
        'middleware' => 'contact.contact.view',
        'level' => 0,
        'icon' => 'user-circle',
        'sort_order' => 100,
        'group' => null,
    ],
    'contact.contacts' => [
        'parent' => 'contact',
        'name' => 'Contacts',
        'route' => 'lawoo.contact.list',
        'middleware' => 'contact.contact.view',
        'level' => 1,
        'sort_order' => 100,
        'group' => null,
    ]
];
