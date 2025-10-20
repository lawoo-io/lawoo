<?php

return [
    'website_blog.blogs' => [
        'parent' => 'website.pages',
        'name' => 'Blog',
        'route' => 'lawoo.website.blog.posts',
        'middleware' => 'website.pages.view',
        'level' => 1,
        'sort_order' => 1000,
        'group' => null
    ],
    'website_blog.posts' => [
        'parent' => 'website_blog.blogs',
        'name' => 'Posts',
        'route' => 'lawoo.website.blog.posts',
        'middleware' => 'website_blog.post.view',
        'level' => 2,
        'sort_order' => 100,
        'group' => null
    ],
    'website_blog.categories' => [
        'parent' => 'website_blog.blogs',
        'name' => 'Categories',
        'route' => 'lawoo.website.blog.categories',
        'middleware' => 'website.category.view',
        'level' => 2,
        'sort_order' => 200,
        'group' => null
    ]
];
