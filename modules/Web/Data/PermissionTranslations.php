<?php

return [
    'Modules\Core\Models\Permission' => [
        'web.profile.view' => [
            'name' => [
                'en' => 'Profile',
                'de' => 'Profil',
            ],
            'description' => [
                'en' => 'Can view own profile settings',
                'de' => 'Kann eigene Profileinstellungen anzeigen',
            ],
        ],
        'web.profile.edit' => [
            'name' => [
                'en' => 'Edit Own Profile',
                'de' => 'Eigenes Profil bearbeiten',
            ],
            'description' => [
                'en' => 'Can edit own profile settings',
                'de' => 'Kann eigene Profileinstellungen bearbeiten',
            ],
        ],
        'web.password.change' => [
            'name' => [
                'en' => 'Change Password',
                'de' => 'Passwort ändern',
            ],
            'description' => [
                'en' => 'Can update own password',
                'de' => 'Kann eigenes Passwort ändern',
            ],
        ],
        'web.appearance.change' => [
            'name' => [
                'en' => 'Change Appearance',
                'de' => 'Erscheinungsbild ändern',
            ],
            'description' => [
                'en' => 'Can update own appearance',
                'de' => 'Kann eigenes Erscheinungsbild ändern',
            ],
        ],
        'web.search.save_for_all' => [
            'name' => [
                'en' => 'Save for All',
                'de' => 'Für alle speichern',
            ],
            'description' => [
                'en' => 'Can save filter for all users',
                'de' => 'Kann Filter für alle Benutzer speichern',
            ],
        ],
        'web.search.delete_public' => [
            'name' => [
                'en' => 'Delete Public Search',
                'de' => 'Öffentliche Suche löschen',
            ],
            'description' => [
                'en' => 'Can delete public search settings.',
                'de' => 'Kann öffentliche Sucheinstellungen löschen',
            ],
        ],
        'web.settings.show' => [
            'name' => [
                'en' => 'Show Settings',
                'de' => 'Einstellungen anzeigen',
            ],
            'description' => [
                'en' => 'Can show settings',
                'de' => 'Kann Einstellungen anzeigen',
            ],
        ],
        'web.settings.roles_permissions.show' => [
            'name' => [
                'en' => 'Show Roles Permissions',
                'de' => 'Rollen und Berechtigungen anzeigen',
            ],
            'description' => [
                'en' => 'Can show roles permissions',
                'de' => 'Kann Rollen und Berechtigungen anzeigen',
            ],
        ],
        'web.settings.roles_permissions.edit' => [
            'name' => [
                'en' => 'Edit Roles and Permissions',
                'de' => 'Rollen und Berechtigungen bearbeiten',
            ],
            'description' => [
                'en' => 'Can edit roles and permissions',
                'de' => 'Kann Rollen und Berechtigungen bearbeiten',
            ],
        ],
        'web.settings.roles.delete' => [
            'name' => [
                'en' => 'Delete Roles',
                'de' => 'Rollen löschen',
            ],
            'description' => [
                'en' => 'Can delete roles',
                'de' => 'Kann Rollen löschen',
            ],
        ],
    ],
];
