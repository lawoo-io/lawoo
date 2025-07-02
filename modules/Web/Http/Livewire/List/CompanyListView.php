<?php

namespace Modules\Web\Http\Livewire\List;


use Modules\Web\Repositories\CompanyRepository;

class CompanyListView extends BaseListView
{

    public ?string $moduleName = 'Web';

    public ?string $modelClass = 'Company';

    protected string $repositoryClass = CompanyRepository::class;

    public array $defaultColumns = ['id', 'name'];

    public string $createViewRoute = 'lawoo.settings.companies.records.create';

    public string $formViewRoute = 'lawoo.settings.companies.records.view';

    public function boot(): void
    {
        $this->title = __t('Companies', 'Web');
        parent::boot();
    }

    public static function setSearchFields(): array
    {
        return [
            'name' => __t('Name', 'Web'),
            'zip' => __t('Zip', 'Web'),
            'city' => __t('City', 'Web'),
        ];
    }

    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'label' => __t('ID', 'Web')
            ],
            'name' => [
                'label' => __t('Name', 'Web'),
            ],
            'zip' => [
                'label' => __t('Zip', 'Web'),
            ],
            'city' => [
                'label' => __t('City', 'Web'),
            ],
            'is_active' => [
                'label' => __t('Active', 'Web'),
                'type' => 'switch',
            ],
        ];
    }
}
