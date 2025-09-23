<?php

namespace Modules\Website\Http\Livewire\Form;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Web\Http\Livewire\Form\BaseFormView;
use Modules\Website\Models\Theme;
use Modules\Website\Repositories\LayoutRepository;
use Modules\Website\Repositories\WebsiteRepository;
use Modules\Website\Services\ContentManager;

class LayoutFormView extends BaseFormView
{
    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = LayoutRepository::class;

    /**
     * !Required
     * @var string
     */
    public string $recordsRoute = 'lawoo.website.layouts.records';

    /**
     * !Required only by Create type
     * @var string
     */
    public string $recordRoute = 'lawoo.website.layouts.records.view';

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
            'path' => [
                'label' => __t('Path', 'Website'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
                'disabled' => true,
            ],
            'internal_note' => [
                'label' => __t('Internal Note', 'Website'),
                'type' => 'textarea',
                'class' => 'lg:col-span-12',
            ],
            'content' => [
                'label' => __t('Content', 'Website'),
                'type' => 'editor',
                'mode' => 'code',
                'languages' => 'html,javascript,css',
                'class' => 'lg:col-span-12',
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
            ],
            'website_id' => [
                'label' => __t('Website', 'Website'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => self::getWebsites(),
            ],
            'is_active' => [
                'label' => __t('Active', 'Website'),
                'type' => 'switch',
                'class' => 'lg:col-span-3',
                'default' => false
            ],
            'auto_public' => [
                'label' => 'Public',
                'type' => 'switch',
                'class' => 'lg:col-span-3',
                'default' => false
            ],
            'is_public' => [
                'label' => 'Public',
                'type' => 'hidden',
                'class' => 'lg:col-span-6',
                'default' => false
            ],
            'is_changed' => [
                'label' => 'Changed',
                'type' => 'hidden',
                'class' => 'lg:col-span-6',
                'default' => false,
            ]
        ];
    }

    public function setRules(): void
    {
        $this->rules = [
            'data.name' => 'required',
            'data.theme_id' => 'required',
        ];
    }

    public function getThemes(): array
    {
        return Theme::all()->pluck('name', 'id')->toArray();
    }

    protected function getWebsites(): array
    {
        $websiteRepository = new WebsiteRepository();
        return $websiteRepository->getFilteredData()->pluck('name', 'id')->toArray();
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

    public function updated($property, $value): void
    {
        if ($property === 'data.is_active' && $value === false) {
            self::unpublish();
        }
    }

    protected function extraHeaderView(): string
    {
        return 'livewire.website.form.layout-extra-header-view';
    }

    protected function update(): ?Model
    {
        $update = parent::update();
        $this->dispatch('reload-form-data');
        return $update;
    }
}
