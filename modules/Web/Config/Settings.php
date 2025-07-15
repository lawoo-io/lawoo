<?php

return [
    'Web' => [
        'name' => 'General Settings',
        'description' => 'Mange the general Settings.',
        'icon' => 'cog',
        'middleware' => 'web.settings.show',
        'fields' => [
            'cache_view_settings_days' => [
                'label' => 'Cache view',
                'description_top' => 'Cache duration for layout views (in days).',
                'value' => 90,
                'type' => 'input',
                'group' => 'Cache',
                'class' => 'col-span-6',
                'rules' => 'required|integer',
            ],
            'cache_settings_records' => [
                'label' => 'Cache Settings Records',
                'description_top' => 'Cache duration for settings records (in days).',
                'value' => 30,
                'type' => 'input',
                'group' => 'Cache',
                'class' => 'col-span-6',
                'rules' => 'required|integer',
            ],
            'file_upload_sharding_enabled' => [
                'label' => 'File upload sharding',
                'description_top' => 'Automatically stores uploaded files in subfolders to improve performance with high upload volumes.',
                'value' => false,
                'type' => 'switch',
                'mode' => 'single',
                'group' => 'File Upload',
                'class' => 'col-span-6',
                'rules' => 'boolean',
            ]
        ]
    ]
];
