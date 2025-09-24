<?php

return [
    'newsletter.campaigns' => [
        'name' => 'Newsletter',
        'route' => 'lawoo.newsletter.campaign.records',
        'middleware' => 'newsletter.campaign.view',
        'level' => 0,
        'icon' => 'newspaper',
        'sort_order' => 300,
        'group' => null,
    ],
    'newsletter.campaigns.records' => [
        'name' => 'Campaigns',
        'parent' => 'newsletter.campaigns',
        'route' => 'lawoo.newsletter.campaign.records',
        'middleware' => 'newsletter.campaign.view',
        'level' => 1,
        'icon' => null,
        'sort_order' => 100,
        'group' => null,
    ],
    'newsletter.subscribers.records' => [
        'name' => 'Subscribers',
        'parent' => 'newsletter.campaigns',
        'route' => 'lawoo.newsletter.subscriber.records',
        'middleware' => 'newsletter.subscriber.view',
        'level' => 1,
        'icon' => null,
        'sort_order' => 200,
        'group' => null,
    ]
];
