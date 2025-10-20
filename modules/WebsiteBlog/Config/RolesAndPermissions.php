<?php

return [
    'website-blog-manager' => [
        'slug' => 'blog-manager',
        'name' => 'Website Blog Manager',
        'description' => 'Manage Website Blogs',
        'modules' => 'website_blog',
        'is_system' => true,
        'permissions' => [
            'website_blog.post.view' => [
                'name' => 'View Posts',
                'description' => 'Manage website blog posts.',
                'resource' => 'website_blog',
                'action' => 'view'
            ],
            'website_blog.post.create' => [
                'name' => 'Create Posts',
                'description' => 'Can create a new blog post.',
                'resource' => 'website_blog',
                'action' => 'create'
            ],
            'website_blog.post.edit' => [
                'name' => 'Edit Posts',
                'description' => 'Can edit a blog post.',
                'resource' => 'website_blog',
                'action' => 'edit'
            ],
            'website_blog.post.delete' => [
                'name' => 'Delete Posts',
                'description' => 'Can delete a blog post.',
                'resource' => 'website_blog',
                'action' => 'delete'
            ],
            'website_blog.category.view' => [
                'name' => 'View Categories',
                'description' => 'View blog categories.',
                'resource' => 'website_blog',
                'action' => 'view'
            ],
            'website_blog.category.create' => [
                'name' => 'Create Categories',
                'description' => 'Can create a new blog category.',
                'resource' => 'website_blog',
                'action' => 'create'
            ],
            'website_blog.category.edit' => [
                'name' => 'Edit Categories',
                'description' => 'Can edit a blog category.',
                'resource' => 'website_blog',
                'action' => 'edit'
            ],
            'website_blog.category.delete' => [
                'name' => 'Delete Categories',
                'description' => 'Can delete a blog category.',
                'resource' => 'website_blog',
                'action' => 'delete'
            ]
        ]
    ],
];
