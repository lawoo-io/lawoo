<?php

namespace Modules\Web\Http\Livewire\List;

use Modules\Web\Repositories\CountryRepository;

class CountryListView extends BaseListView
{
    public ?string $moduleName = 'Web';
    public ?string $modelClass = 'Country';

    protected string $repositoryClass = CountryRepository::class;

    public array $defaultColumns = ['id', 'name'];

    public string $createViewRoute = 'lawoo.settings.countries.create';

    public string $formViewRoute = 'lawoo.settings.countries.view';

    public function boot(): void
    {
        $this->title = __t('Countries', 'Web');
        parent::boot();
    }

    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'label' => __t('ID', 'Web'),
            ],
            'name' => [
                'label' => __t('Name', 'Web'),
            ],
            'is_active' => [
                'label' => __t('Active', 'Web'),
                'type' => 'switch',
            ]
        ];
    }
}
