<?php

return [
    'super-admin' => [
        'slug' => 'super-admin',
        'name' => 'Super Administrator',
        'description' => 'Super Administrator Role',
        'modules' => 'web',
        'is_system' => true,
        'permissions' => [
            'user.profile.view' => [
                'name' => 'Profile',
                'description' => 'Can view own profile settings',
                'resource' => 'profile',
                'action' => 'view',
            ],
            'user.profile.edit' => [
                'name' => 'Edit Own Profile',
                'description' => 'Can edit own profile settings',
                'resource' => 'profile',
                'action' => 'edit',
            ],
            'user.password.change' => [
                'name' => 'Change Password',
                'description' => 'Can update own password',
                'resource' => 'password',
                'action' => 'change',
            ]
        ],
    ],

    // Standard User Role - for all registered users
    'user' => [
        'slug' => 'user',
        'name' => 'Standard User',
        'description' => 'Basic user permissions for all authenticated users',
        'modules' => 'web',
        'is_system' => true,
        'permissions' => [
            'user.profile.view' => [],
            'user.profile.edit' => [],
            'user.password.change' => []
        ]
    ]
];
