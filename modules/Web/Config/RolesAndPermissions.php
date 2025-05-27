<?php

return [
    'super-admin' => [
        'slug' => 'super-admin',
        'name' => Lang::get('security.super_admin.role.label'),
        'description' => Lang::get('security.super_admin.role.description'),
        'modules' => 'web',
        'is_system' => true,
        'permissions' => [
            'web.dashboard.view' => [
                'name' => Lang::get('security.super_admin.permissions.web_dashboard_view.label'),
                'description' => Lang::get('security.super_admin.permissions.web_dashboard_view.description'),
            ]

        ],
    ]
];
