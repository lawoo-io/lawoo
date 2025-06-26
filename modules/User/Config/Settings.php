<?php

return [
    'User' => [
        'name' => 'Users',
        'icon' => 'users',
        'middleware' => 'user.settings.show',
        'fields' => [
            'test_setting_one' => [
                'label' => 'Test Setting 1',
                'description_top' => 'Test setting 1',
                'value' => 1,
                'type' => 'select',
                'options' => [
                    1 => 'Option 1',
                    2 => 'Option 2',
                    3 => 'Option 3',
                ],
                'class' => 'col-span-6',
                'rules' => 'required',
            ],
        ]
    ]
];
