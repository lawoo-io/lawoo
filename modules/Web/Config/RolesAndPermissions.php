<?php

return [
    'super-admin' => [
        'slug' => 'super-admin',
        'name' => 'Super Administrator',
        'description' => 'Super Administrator Role',
        'modules' => 'web',
        'is_system' => true,
        'permissions' => [
            'web.profile.view' => [
                'name' => 'Profile',
                'description' => 'Can view own profile settings',
                'resource' => 'profile',
                'action' => 'view',
            ],
            'web.profile.edit' => [
                'name' => 'Edit Own Profile',
                'description' => 'Can edit own profile settings',
                'resource' => 'profile',
                'action' => 'edit',
            ],
            'web.password.change' => [
                'name' => 'Change Password',
                'description' => 'Can update own password',
                'resource' => 'password',
                'action' => 'change',
            ],
            'web.appearance.change' => [
                'name' => 'Change Appearance',
                'description' => 'Can update own appearance',
                'resource' => 'appearance',
                'action' => 'change',
            ],
            'web.search.save_for_all' => [
                'name' => 'Save for All',
                'description' => 'Can save filter for all users',
                'resource' => 'search',
                'action' => 'save',
            ],
            'web.search.delete_public' => [
                'name' => 'Delete Public Search',
                'description' => 'Can delete public search settings.',
                'resource' => 'search',
                'action' => 'delete',
            ],
            'web.settings.show' => [
                'name' => 'Show Settings',
                'description' => 'Can show settings',
                'resource' => 'settings',
                'action' => 'show',
            ],
            'web.settings.roles_permissions.show' => [
                'name' => 'Show Roles Permissions',
                'description' => 'Can show roles permissions',
                'resource' => 'roles_permissions',
                'action' => 'show',
            ],
            'web.settings.roles_permissions.edit' => [
                'name' => 'Edit Roles and Permissions',
                'description' => 'Can edit roles and permissions',
                'resource' => 'roles',
                'action' => 'edit',
            ],
            'web.settings.roles.delete' => [
                'name' => 'Delete Roles',
                'description' => 'Can delete roles',
                'resource' => 'roles',
                'action' => 'delete',
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
            'web.profile.view' => [],
            'web.profile.edit' => [],
            'web.password.change' => [],
            'web.appearance.change' => [],
        ]
    ]
];
