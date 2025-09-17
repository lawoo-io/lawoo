<?php

return [
    'website-manager' => [
        'slug' => 'website-manager',
        'name' => 'Website Manager',
        'description' => 'Manage all websites',
        'modules' => 'website',
        'is_system' => true,
        'permissions' => [
            'website.website.view' => [
                'name' => 'Websites View',
                'description' => 'Manage websites',
                'resource' => 'website',
                'action' => 'view',
            ],
            'website.website.create' => [
                'name' => 'Websites Create',
                'description' => 'Can create a new website',
                'resource' => 'website',
                'action' => 'create',
            ],
            'website.website.edit' => [
                'name' => 'Websites Edit',
                'description' => 'Can edit Websites',
                'resource' => 'website',
                'action' => 'edit',
            ],
            'website.website.delete' => [
                'name' => 'Websites delete',
                'description' => 'Can delete Websites',
                'resource' => 'website',
                'action' => 'delete',
            ],
            'website.pages.view' => [
                'name' => 'Pages View',
                'description' => 'Manage website pages',
                'resource' => 'pages',
                'action' => 'view'
            ],
            'website.pages.create' => [
                'name' => 'Pages Create',
                'description' => 'Can create new website pages',
                'resource' => 'pages',
                'action' => 'create'
            ],
            'website.pages.edit' => [
                'name' => 'Pages Edit',
                'description' => 'Can edit website pages',
                'resource' => 'pages',
                'action' => 'edit'
            ],
            'website.pages.delete' => [
                'name' => 'Pages Delete',
                'description' => 'Can delete website pages',
                'resource' => 'pages',
                'action' => 'delete'
            ],
            'website.layouts.view' => [
                'name' => 'Layout View',
                'description' => 'Show website layouts',
                'resource' => 'layouts',
                'action' => 'view'
            ],
            'website.layouts.create' => [
                'name' => 'Layout Create',
                'description' => 'Create website layouts',
                'resource' => 'layouts',
                'action' => 'create'
            ],
            'website.layouts.edit' => [
                'name' => 'Layout Edit',
                'description' => 'Edit website layouts',
                'resource' => 'layouts',
                'action' => 'edit'
            ],
            'website.layouts.delete' => [
                'name' => 'Layout delete',
                'description' => 'Delete website layouts',
                'resource' => 'layouts',
                'action' => 'delete'
            ],
            'website.theme.view' => [
                'name' => 'Themes View',
                'description' => 'Show Website Themes',
                'resource' => 'theme',
                'action' => 'view',
            ],
            'website.theme.create' => [
                'name' => 'Themes Create',
                'description' => 'Can create a new website theme',
                'resource' => 'theme',
                'action' => 'create',
            ],
            'website.theme.edit' => [
                'name' => 'Themes Edit',
                'description' => 'Can edit Website Themes',
                'resource' => 'theme',
                'action' => 'edit',
            ],
            'website.theme.delete' => [
                'name' => 'Themes delete',
                'description' => 'Can delete Website Themes',
                'resource' => 'theme',
                'action' => 'delete',
            ],
            'website.assets.view' => [
                'name' => 'Assets View',
                'description' => 'Show Website Assets',
                'resource' => 'assets',
                'action' => 'view',
            ],
            'website.assets.create' => [
                'name' => 'Assets Create',
                'description' => 'Can create a new website assets',
                'resource' => 'assets',
                'action' => 'create',
            ],
            'website.assets.edit' => [
                'name' => 'Assets Edit',
                'description' => 'Can edit Website Assets',
                'resource' => 'assets',
                'action' => 'edit',
            ],
            'website.assets.delete' => [
                'name' => 'Assets delete',
                'description' => 'Can delete Website Assets',
                'resource' => 'assets',
                'action' => 'delete',
            ],
        ]
    ]
];
