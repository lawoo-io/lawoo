<?php

namespace Modules\Website\Http\Livewire\List;


use Modules\Web\Http\Livewire\List\BaseListView;
use Modules\Website\Repositories\WebsiteRepository;

class WebsiteListView extends BaseListView
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
    public ?string $modelClass = 'Website';

    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = WebsiteRepository::class;

    /**
     * @var array
     */
    public array $defaultColumns = ['id', 'name', 'url', 'is_active'];

    /**
     * @var string
     */
    public string $createViewRoute = 'lawoo.website.records.create';

    /**
     * @var string
     */
    public string $formViewRoute = 'lawoo.website.records.view';

    /**
     * Function boot
     */
    public function boot(): void
    {
        $this->title = __t('Websites', 'Website');
        parent::boot();
    }

    /**
     * Function setSearchFields
     */
    public static function setSearchFields(): array
    {
        return [
            'name' => __t('Name', 'Website'),
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
            'url' => [
                'label' => __t('URL', 'Website'),
            ],
            'is_active' => [
                'label' => __t('Active', 'Website'),
                'type' => 'switch',
            ]
        ];
    }
}
