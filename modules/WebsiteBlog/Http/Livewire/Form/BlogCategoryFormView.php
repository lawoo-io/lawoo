<?php

namespace Modules\WebsiteBlog\Http\Livewire\Form;

use Modules\Web\Http\Livewire\Form\BaseFormView;
use Modules\Website\Repositories\WebsiteRepository;
use Modules\WebsiteBlog\Repositories\BlogCategoryRepository;

class BlogCategoryFormView extends BaseFormView
{
    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = BlogCategoryRepository::class;

    /**
     * !Required
     * @var string
     */
    public string $recordsRoute = 'lawoo.website.blog.categories';

    /**
     * !Required only by Create type
     * @var string
     */
    public string $recordRoute = 'lawoo.website.blog.categories.update';

    /**
     * Only by TrackableModel
     * @var bool
     */
    public bool $showMessages = false;


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
                'class' => 'lg:col-span-6',
            ],
            'short_description' => [
                'label' => __t('Short Description', 'WebsiteBlog'),
                'type' => 'textarea',
                'class' => 'lg:col-span-12',
            ],
            'meta_title' => [
                'label' => __t('Meta Title', 'WebsiteBlog'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'meta_description' => [
                'label' => __t('Meta Description', 'WebsiteBlog'),
                'type' => 'textarea',
                'class' => 'lg:col-span-6',
            ],
            'robot_index' => [
                'label' => __t('Index', 'WebsiteBlog'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => [
                    'index' => __t('index', 'WebsiteBlog'),
                    'noindex' => __t('noindex', 'WebsiteBlog'),
                ]
            ],
            'robot_follow' => [
                'label' => __t('Follow', 'WebsiteBlog'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => [
                    'follow' => __t('follow', 'WebsiteBlog'),
                    'nofollow' => __t('nofollow', 'WebsiteBlog'),
                ]
            ],
            'website_id' => [
                'label' => __t('Website', 'WebsiteBlog'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => self::getWebsites(),
            ],
            'company_id' => [
                'label' => __t('Company', 'WebsiteBlog'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => self::getCompanies()
            ],
            'is_active' => [
                'label' => __t('Active', 'WebsiteBlog'),
                'type' => 'switch',
                'class' => 'lg:col-span-6',
                'default' => false,
            ],
            'is_public' => [
                'label' => __t('Public', 'WebsiteBlog'),
                'type' => 'hidden',
                'class' => 'lg:col-span-6',
                'default' => false,
            ],
        ];
    }

    protected function extraHeaderView(): string
    {
        return 'livewire.website-blog.form.blog-category-extra-header-view';
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

}
