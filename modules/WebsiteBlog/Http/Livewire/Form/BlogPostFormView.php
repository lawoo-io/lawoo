<?php

namespace Modules\WebsiteBlog\Http\Livewire\Form;

use Modules\Web\Http\Livewire\Form\BaseFormView;
use Modules\Website\Repositories\WebsiteRepository;
use Modules\WebsiteBlog\Models\BlogCategory;
use Modules\WebsiteBlog\Repositories\BlogPostRepository;

class BlogPostFormView extends BaseFormView
{
    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = BlogPostRepository::class;

    /**
     * !Required
     * @var string
     */
    public string $recordsRoute = 'lawoo.website.blog.posts';

    /**
     * !Required only by Create type
     * @var string
     */
    public string $recordRoute = 'lawoo.website.blog.posts.update';

    /**
     * Only by TrackableModel
     * @var bool
     */
    public bool $showMessages = false;

    public string $permissionForShow = 'website_blog.post.view';

    public string $permissionForEdit = 'website_blog.post.edit';

    /**
     * Set Fields function
     */
    public function setFields(): void
    {
        $this->fields = [
            'name' => [
                'label' => __t('Name', 'WebsiteBlog'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
                'blur' => 'generateSlugFromName',
            ],
            'slug' => [
                'label' => __t('Slug', 'WebsiteBlog'),
                'type' => 'input',
                'class' => 'lg:col-span-4',
            ],
            'image' => [
                'label' => __t('Image', 'WebsiteBlog'),
                'type' => 'avatar',
                'class' => 'lg:col-span-2 flex justify-end',
            ],
            'short_description' => [
                'label' => __t('Short description', 'WebsiteBlog'),
                'type' => 'textarea',
                'class' => 'lg:col-span-6',
            ],
            'blog_category_id' => [
                'label' => __t('Blog Category', 'WebsiteBlog'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => self::getCategories()
            ],
            'tabs' => [
                'tab_one' => [
                    'label' => __t('Content', 'WebsiteBlog'),
                    'class' => 'w-full grid grid-cols-1 lg:grid-cols-12 gap-4',
                    'fields' => [
                        'content' => [
                            'label' => __t('Content', 'WebsiteBlog'),
                            'type' => 'editor',
                            'mode' => 'text',
                            'class' => 'lg:col-span-12',
                        ]
                    ]
                ],
                'tab_two' => [
                    'label' => __t('SEO', 'WebsiteBlog'),
                    'class' => 'w-full grid grid-cols-1 lg:grid-cols-12 gap-4',
                    'fields' => [
                        'meta_title' => [
                            'label' => __t('Title', 'WebsiteBlog'),
                            'type' => 'input',
                            'class' => 'lg:col-span-6',
                        ],
                        'meta_description' => [
                            'label' => __t('Description', 'WebsiteBlog'),
                            'type' => 'textarea',
                            'class' => 'lg:col-span-6',
                        ],
                        'robot_index' => [
                            'label' => __t('Robot index', 'WebsiteBlog'),
                            'type' => 'select',
                            'class' => 'lg:col-span-6',
                            'options' => [
                                'index' => __t('index', 'WebsiteBlog'),
                                'noindex' => __t('noindex', 'WebsiteBlog'),
                            ]
                        ],
                        'robot_follow' => [
                            'label' => __t('Robot follow', 'WebsiteBlog'),
                            'type' => 'select',
                            'class' => 'lg:col-span-6',
                            'options' => [
                                'follow' => __t('follow', 'WebsiteBlog'),
                                'nofollow' => __t('nofollow', 'WebsiteBlog'),
                            ]
                        ]
                    ]
                ],
                'tab_three' => [
                    'label' => __t('Media', 'WebsiteBlog'),
                    'class' => 'w-full grid grid-cols-1 lg:grid-cols-12 gap-4',
                    'fields' => [

                        'images' => [
                            'type' => 'fileUpload',
                            'label' => __t('Images', 'WebsiteBlog'),
                            'class' => 'lg:col-span-6',
                            'multiple' => true,
                            'show_preview' => true,
                            'set_to_public' => true,
                        ],
                        'documents' => [
                            'type' => 'fileUpload',
                            'label' => __t('Documents', 'WebsiteBlog'),
                            'class' => 'lg:col-span-6',
                            'multiple' => true,
                            'show_preview' => true,
                            'set_to_public' => true,
                        ],
                    ]
                ]
            ],
            'website_id' => [
                'label' => __t('Website', 'WebsiteBlog'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => self::getWebsites()
            ],
            'company_id' => [
                'label' => __t('Company', 'WebsiteBlog'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => self::getCompanies()
            ],
            'is_public' => [
                'label' => __t('Public', 'WebsiteBlog'),
                'type' => 'hidden',
                'class' => 'lg:col-span-6',
                'default' => false,
            ]
        ];
    }

    protected function extraHeaderView(): string
    {
        return 'livewire.website-blog.form.blog-post-extra-header-view';
    }


    public function publish(): void
    {
        $this->data['is_public'] = true;
        $this->update();
    }

    public function unpublish(): void
    {
        $this->data['is_public'] = false;
        $this->update();
    }

    protected function getWebsites(): array
    {
        $websiteRepository = new WebsiteRepository();
        return $websiteRepository->getFilteredData()->pluck('name', 'id')->toArray();
    }

    protected function getCategories(): array
    {
        $categories = BlogCategory::where('is_active', true)->get()->map(function (BlogCategory $category) {
            return [
                'name' => $category->name,
                'id' => $category->id,
            ];
        })->pluck('name', 'id')->toArray();
        return $categories;
    }

}
