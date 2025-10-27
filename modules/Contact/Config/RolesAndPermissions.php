<?php

return [
    'contact-manager' => [
        'slug' => 'contact-manager',
        'name' => 'Contact Manager',
        'description' => 'Manage contacts',
        'modules' => 'contact',
        'is_system' => true,
        'permissions' => [
            'contact.contact.view' => [
                'name' => 'View Contacts',
                'description' => 'Show contacts',
                'resource' => 'contact',
                'action' => 'view',
            ],
            'contact.contact.create' => [
                'name' => 'Create Contact',
                'description' => 'Create contacts',
                'resource' => 'contact',
                'action' => 'create',
            ],
            'contact.contact.edit' => [
                'name' => 'Edit Contact',
                'description' => 'Edit contacts',
                'resource' => 'contact',
                'action' => 'edit',
            ],
            'contact.contact.delete' => [
                'name' => 'Delete Contact',
                'description' => 'Delete contacts',
                'resource' => 'contact',
                'action' => 'delete',
            ]
        ],
    ],
];
