<?php

namespace Modules\Web\Http\Livewire\List;


use Modules\Web\Repositories\LanguageRepository;

class LanguageListView extends BaseListView
{
    public ?string $moduleName = 'Web';
    public ?string $modelClass = 'Language';

    protected string $repositoryClass = LanguageRepository::class;

    public array $defaultColumns = ['id', 'name'];

    public function boot(): void
    {
        $this->title = __t('Languages', 'Web');
        parent::boot();
    }

    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'label' => __t('ID', 'Web')
            ],
            'name' => [
                'label' => __t('Name', 'Web')
            ],
            'code' => [
                'label' => __t('Code', 'Web')
            ],
            'is_active' => [
                'label' => __t('Active', 'Web'),
                'type' => 'switch'
            ],
            'is_default' => [
                'label' => __t('Default', 'Web'),
                'type' => 'switch'
            ]
        ];
    }

}
