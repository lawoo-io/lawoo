<?php

namespace Modules\Website\Http\Livewire\Form;

use Modules\Web\Http\Livewire\Form\BaseFormView;
use Modules\Website\Repositories\ThemeRepository;

class ThemeFormView extends BaseFormView
{
    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = ThemeRepository::class;

    public string $permissionForShow = 'website.theme.view';
    public string $permissionForEdit = 'website.theme.edit';
    public string $permissionForDeleting = 'website.theme.delete';

    /**
     * !Required
     * @var string
     */
    public string $recordsRoute = 'lawoo.website.themes.records';

    /**
     * !Required only by Create type
     * @var string
     */
    public string $recordRoute = 'lawoo.website.themes.records.view';

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
            'system_name' => [
                'label' => __t('System name', 'Website'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'short_description' => [
                'label' => __t('Short description', 'Website'),
                'type' => 'textarea',
                'rows' => 2,
                'class' => 'lg:col-span-12',
            ],
            'image' => [
                'type' => 'fileUploader',
                'mode' => 'image',
                'accept' => '/image/*',
                'imageClass' => 'w-20 h-20',
                'model' => '',
                'label' => __t('Preview', 'Website'),
                'class' => 'lg:col-span-6',
            ],
            'images' => [
                'type' => 'fileUploader',
                'mode' => 'images',
                'glightbox' => true,
                'accept' => 'image/*',
                'imageClass' => 'w-20 h-20',
                'model' => '',
                'label' => __t('Images', 'Website'),
                'class' => 'lg:col-span-6',
            ],
            'description' => [
                'label' => __t('Description', 'Website'),
                'type' => 'editor',
                'mode' => 'code',
                'languages' => 'html,javascript,css',
                'class' => 'lg:col-span-12',
            ],
            'author_name' => [
                'label' => __t('Author', 'Website'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'author_website' => [
                'label' => __t('Website', 'Website'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ]
        ];
    }

    public function setRules(): void
    {
        $id = 1000;
        if ($this->record) $id = $this->record['id'];
        $this->rules = [
            'data.name' => 'required|max:150',
            'data.system_name' => 'required|max:150|unique:themes,system_name,' . $id,
            'data.author_name' => 'max:256',
            'data.author_website' => 'max:256',
        ];
    }

}
