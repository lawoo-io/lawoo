<?php

namespace Modules\Web\Http\Livewire\Form;

use Modules\Web\Repositories\ModuleUiTranslationRepository;

class TranslationFormView extends BaseFormView
{
    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = ModuleUiTranslationRepository::class;

    /**
     * !Required
     * @var string
     */
    public string $recordsRoute = 'lawoo.settings.translations';

    /**
     * !Required only by Create type
     * @var string
     */
    public string $recordRoute = 'lawoo.settings.translations.view';

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
            'key_string' => [
                'label' => __t('Original value', 'Web'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
                'disabled' => true,
            ],
            'translated_value' => [
                'label' => __t('Translated value', 'Web'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'module' => [
                'label' => __t('Module', 'Web'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
                'disabled' => true,
            ],
            'locale' => [
                'label' => __t('Locale', 'Web'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
                'disabled' => true,
            ],
            'is_auto_created' => [
                'label' => __t('Auto created', 'Web'),
                'type' => 'switch',
                'class' => 'lg:col-span-6',
            ],
            'removed' => [
                'label' => __t('Removed', 'Web'),
                'type' => 'switch',
                'class' => 'lg:col-span-6',
                'disabled' => true,
            ]
        ];
    }

}
