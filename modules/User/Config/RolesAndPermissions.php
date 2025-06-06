<?php

return [
    'user-manager' => [
        'slug' => 'user-manager',
        'name' => 'User Manager',
        'description' => 'Manage user roles and permissions.',
        'modules' => 'user',
        'is_system' => false,
        'permissions' => [
            'user.users.show' => [
                'name' => 'Show Users',
                'description' => 'Show all users.',
                'resource' => 'users',
                'action' => 'show',
            ],
            'user.users.create' => [
                'name' => 'Create User',
                'description' => 'Create new user.',
                'resource' => 'users',
                'action' => 'create',
            ],
            'user.users.edit' => [
                'name' => 'Edit User',
                'description' => 'Edit user.',
                'resource' => 'users',
                'action' => 'edit',
            ],
            'user.users.delete' => [
                'name' => 'Delete User',
                'description' => 'Delete user.',
                'resource' => 'users',
                'action' => 'delete',
            ]
        ]
    ],
];
