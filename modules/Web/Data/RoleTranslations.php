<?php

return [
    'Modules\Core\Models\Role' => [
        'Super Administrator' => [
            'name' => [
                'en' => 'Super Administrator',
                'de' => 'Super Administrator',
            ],
            'description' => [
                'en' => 'Super Administrator Role',
                'de' => 'Rolle des Superadministrators',
            ],
        ],
        'Standard User' => [
            'name' => [
                'en' => 'Standard User',
                'de' => 'Standard Benutzer',
            ],
            'description' => [
                'en' => 'Basic user permissions for all authenticated users',
                'de' => 'Basisrechte für alle registrierten internen Benutzer',
            ]
        ]
    ]
];
