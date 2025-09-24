<?php

return [
    'newsletter-manager' => [
        'name' => 'Newsletter Manager',
        'slug' => 'newsletter-manager',
        'description' => 'Manage newsletter',
        'modules' => 'newsletter',
        'is_system' => true,
        'permissions' => [
            'newsletter.campaign.view' => [
                'name' => 'Campaigns View',
                'description' => 'Manage campaigns',
                'resource' => 'newsletter',
                'action' => 'view'
            ],
            'newsletter.campaign.create' => [
                'name' => 'Campaigns Create',
                'description' => 'Can create a new campaign',
                'resource' => 'newsletter',
                'action' => 'create'
            ],
            'newsletter.campaign.edit' => [
                'name' => 'Campaigns Edit',
                'description' => 'Can edit a new campaign',
                'resource' => 'newsletter',
                'action' => 'edit'
            ],
            'newsletter.campaign.delete' => [
                'name' => 'Campaigns Delete',
                'description' => 'Can delete a new campaign',
                'resource' => 'newsletter',
                'action' => 'delete'
            ],
            'newsletter.subscriber.view' => [
                'name' => 'Subscribers View',
                'description' => 'Manage subscribers',
                'resource' => 'newsletter',
                'action' => 'view'
            ],
            'newsletter.subscriber.create' => [
                'name' => 'Subscriber Create',
                'description' => 'Can create a new subscriber',
                'resource' => 'newsletter',
                'action' => 'create'
            ],
            'newsletter.subscriber.edit' => [
                'name' => 'Subscriber Edit',
                'description' => 'Can edit a subscriber',
                'resource' => 'newsletter',
                'action' => 'edit'
            ],
            'newsletter.subscriber.delete' => [
                'name' => 'Subscriber Delete',
                'description' => 'Can delete a subscriber',
                'resource' => 'newsletter',
                'action' => 'delete',
            ]
        ]
    ]
];
