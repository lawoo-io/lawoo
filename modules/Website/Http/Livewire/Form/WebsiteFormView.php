<?php

namespace Modules\Website\Http\Livewire\Form;

use Illuminate\Support\Str;
use Modules\Web\Http\Livewire\Form\BaseFormView;
use Modules\Website\Repositories\ThemeRepository;
use Modules\Website\Repositories\WebsiteRepository;

class WebsiteFormView extends BaseFormView
{
    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = WebsiteRepository::class;

    /**
     * !Required
     * @var string
     */
    public string $recordsRoute = 'lawoo.website.records';

    /**
     * !Required only by Create type
     * @var string
     */
    public string $recordRoute = 'lawoo.website.records.view';

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
                'label' => __t('Name', 'Web'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
                'blur' => 'generateSlugFromName'
            ],
            'slug' => [
                'label' => __t('Slug', 'Website'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
                'disabled' => true
            ],
            'url' => [
                'label' => __t('URL', 'Website'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'is_active' => [
                'label' => __t('Active', 'Website'),
                'type' => 'switch',
                'class' => 'lg:col-span-6',
                'default' => false,
            ],
            'meta_title' => [
                'label' => __t('Meta Title', 'Website'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'meta_description' => [
                'label' => __t('Meta Description', 'Website'),
                'type' => 'textarea',
                'class' => 'lg:col-span-6',
            ],
            'theme_id' => [
                'label' => __t('Theme', 'Website'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => self::getThemes(),
            ],
            'company_id' => [
                'label' => __t('Company', 'Website'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => self::getCompanies(),
            ]
        ];
    }

    // Replace from Base
    public function generateSlugFromName(): void
    {
        if(isset($this->data['name']) && !empty($this->data['name']) && empty($this->data['slug'])) {
            $this->data['slug'] = Str::slug($this->data['name']);
        }
    }

    public function getThemes(): array
    {
        $themeRepository = app(ThemeRepository::class);
        return $themeRepository->all()->pluck('name', 'id')->toArray();
    }

    public function setRules(): void
    {
        $this->rules = [
            'data.name' => 'required|min:3|max:256',
            'data.slug' => 'required',
        ];
    }

}
