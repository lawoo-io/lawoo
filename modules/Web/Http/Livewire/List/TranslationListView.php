<?php

namespace Modules\Web\Http\Livewire\List;


use Modules\Web\Repositories\ModuleUiTranslationRepository;

class TranslationListView extends BaseListView
{
    /**
    * !Required
    * @var string
    */
    public ?string $moduleName = 'Web';

    /**
     * !Required
     * @var string
     */
    public ?string $modelClass = '';

    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = ModuleUiTranslationRepository::class;

    /**
     * @var array
     */
    public array $defaultColumns = ['id', 'key_string'];

    /**
     * @var string
     */
    public string $createViewRoute = '';

    /**
     * @var string
     */
    public string $formViewRoute = 'lawoo.settings.translations.view';

    /**
     * Function boot
     */
    public function boot(): void
    {
        $this->title = __t('Translations', 'Web');
        parent::boot();
    }

    /**
     * Function setSearchFields
     */
    public static function setSearchFields(): array
    {
        return [
            'key_string' => __t('Original value', 'Web'),
            'translated_value' => __t('Translated value', 'Web'),
            'module' => __t('Module', 'Web'),
            'locale' => __t('Locale', 'Web'),
        ];
    }

    /**
     * Function getAvailableColumns
     */
    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'label' => __t('ID', 'Web')
            ],
            'key_string' => [
                'label' => __t('Name', 'Web'),
            ],
            'translated_value' => [
                'label' => __t('Translated', 'Web'),
            ],
            'module' => [
                'label' => __t('Module', 'Web'),
            ],
            'locale' => [
                'label' => __t('Locale', 'Web'),
            ]
        ];
    }
}
