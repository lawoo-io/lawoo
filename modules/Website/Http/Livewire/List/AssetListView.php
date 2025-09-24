<?php

namespace Modules\Website\Http\Livewire\List;


use Modules\Web\Http\Livewire\List\BaseListView;
use Modules\Website\Repositories\AssetRepository;

class AssetListView extends BaseListView
{
    /**
    * !Required
    * @var string
    */
    public ?string $moduleName = 'Website';

    /**
     * !Required
     * @var string
     */
    public ?string $modelClass = 'Asset';

    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = AssetRepository::class;

    /**
     * @var array
     */
    public array $defaultColumns = ['id', 'name', 'type'];

    /**
     * @var string
     */
    public string $createViewRoute = 'lawoo.website.assets.records.create';

    /**
     * @var string
     */
    public string $formViewRoute = 'lawoo.website.assets.records.view';

    /**
     * Function boot
     */
    public function boot(): void
    {
        $this->title = __t('Assets', 'Website');
        parent::boot();
    }

    /**
     * Function setSearchFields
     */
    public static function setSearchFields(): array
    {
        return [
            'name' => __t('Name', 'Website'),
            'type' => __t('Type', 'Website'),
        ];
    }

    public static function setAvailableFilters(): array
    {
        return [
            'column1' => [
                'label' => __t('Filter', 'Website'),
                'column' => 1,
                'filters' => [
                    'is_active' => [
                        'label' => __t('Active', 'Website'),
                        'type' => 'boolean',
                    ],
                ]
            ],
            'column2' => [
                'label' => __t('Filter', 'Website'),
                'column' => 2,
                'filters' => [
                    'type' => [
                        'label' => __t('Type', 'Website'),
                        'type' => 'select',
                        'options' => ['css' => 'CSS', 'js' => 'Javascript', 'scss' => 'SCSS'],
                    ]
                ]
            ]
        ];
    }

    /**
     * Function getAvailableColumns
     */
    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'label' => __t('ID', 'Website')
            ],
            'name' => [
                'label' => __t('Name', 'Website'),
            ],
            'type' => [
                'label' => __t('Type', 'Website'),
            ]
        ];
    }
}
