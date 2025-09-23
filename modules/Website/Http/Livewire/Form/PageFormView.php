<?php

namespace Modules\Website\Http\Livewire\Form;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Web\Http\Livewire\Form\BaseFormView;
use Modules\Website\Models\Layout;
use Modules\Website\Repositories\PageRepository;
use Modules\Website\Repositories\WebsiteRepository;
use Modules\Website\Services\ContentManager;

class PageFormView extends BaseFormView
{
    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = PageRepository::class;

    /**
     * !Required
     * @var string
     */
    public string $recordsRoute = 'lawoo.website.pages.records';

    /**
     * !Required only by Create type
     * @var string
     */
    public string $recordRoute = 'lawoo.website.pages.records.view';

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
            ],
            'url' => [
                'label' => __t('URL', 'Website'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'internal_note' => [
                'label' => __t('Internal Note', 'Website'),
                'type' => 'textarea',
                'class' => 'lg:col-span-6',
            ],
            'layout_id' => [
                'label' => __t('Layout', 'Website'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => self::getLayouts()
            ],
            'tabs' => [
                'tab_one' => [
                    'label' => __t('Content', 'Website'),
                    'class' => 'w-full grid grid-cols-1 lg:grid-cols-12 gap-4',
                    'fields' => [
                        'content' => [
                            'label' => __t('Content', 'Website'),
                            'type' => 'editor',
                            'mode' => 'code',
                            'languages' => 'html,javascript,css',
                            'class' => 'lg:col-span-12',
                        ]
                    ]
                ],
                'tab_two' => [
                    'label' => __t('SEO', 'Website'),
                    'class' => 'w-full grid grid-cols-1 lg:grid-cols-12 gap-4 items-start',
                    'fields' => [
                        'meta_title' => [
                            'label' => __t('Meta Title', 'Website'),
                            'type' => 'input',
                            'class' => 'lg:col-span-6',
                        ],
                        'meta_description' => [
                            'label' => __t('Meta Description', 'Website'),
                            'type' => 'textarea',
                            'rows' => 2,
                            'class' => 'lg:col-span-6',
                        ]
                    ]
                ],
                'tab_three' => [
                    'label' => __t('Additional Information', 'Website'),
                    'class' => 'w-full grid grid-cols-1 lg:grid-cols-12 gap-4 items-start',
                    'fields' => [
                        'company_id' => [
                            'label' => __t('Company', 'Website'),
                            'type' => 'select',
                            'class' => 'lg:col-span-6',
                            'options' => self::getCompanies()
                        ],
                        'website_id' => [
                            'label' => __t('Website', 'Website'),
                            'type' => 'select',
                            'class' => 'lg:col-span-6',
                            'options' => self::getWebsites()
                        ]
                    ]
                ]
            ],
            'is_active' => [
                'label' => __t('Active', 'Website'),
                'type' => 'switch',
                'class' => 'lg:col-span-3',
                'default' => false,
            ],
            'auto_public' => [
                'label' => __t('Publish automatically', 'Website'),
                'type' => 'switch',
                'class' => 'lg:col-span-3',
                'default' => false,
            ],
            'path' => [
                'label' => __t('Path', 'Website'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
                'disabled' => true,
            ],
            'is_public' => [
                'label' => __t('Public', 'Website'),
                'type' => 'hidden',
                'class' => 'lg:col-span-6',
                'default' => false,
            ],
            'is_changed' => [
                'label' => __t('Changed', 'Website'),
                'type' => 'hidden',
                'class' => 'lg:col-span-6',
                'default' => false,
            ],
        ];
    }

    public function setRules(): void
    {
        $this->rules = [
            'data.name' => 'required|min:3|max:256',
            'data.url' => 'required',
            'data.layout_id' => 'required',
            'data.content' => 'required',
            'data.website_id' => 'required',
        ];
    }

    public function getLayouts(): array
    {
        return Layout::all()->pluck('name', 'id')->toArray();
    }

    protected function getWebsites(): array
    {
        $websiteRepository = new WebsiteRepository();
        return $websiteRepository->getFilteredData()->pluck('name', 'id')->toArray();
    }

    protected function extraHeaderView(): string
    {
        return 'livewire.website.form.page-extra-header-view';
    }

    public function publish(): void
    {
        ContentManager::publish($this->record);
        $this->data['is_public'] = true;
        $this->data['is_changed'] = false;
        if (!$this->data['path']) $this->data['path'] = Str::slug($this->record->name);
        $this->update();
    }

    public function unpublish(): void
    {
        ContentManager::unpublish($this->record);
        $this->data['is_public'] = false;
        $this->data['is_changed'] = false;
        if ($this->data['path']) $this->data['path'] = '';
        $this->update();
    }

    public function updated($property, $value)
    {
        if ($property === 'data.is_active' && $value === false) {
            self::unpublish();
        }
    }

    protected function update(): ?Model
    {
        $update = parent::update();
        $this->dispatch('reload-form-data');
        return $update;
    }
}
