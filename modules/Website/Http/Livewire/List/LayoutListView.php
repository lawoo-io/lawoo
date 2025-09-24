<?php

namespace Modules\Website\Http\Livewire\List;


use Modules\Web\Http\Livewire\List\BaseListView;
use Modules\Website\Repositories\LayoutRepository;

class LayoutListView extends BaseListView
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
    public ?string $modelClass = 'Layout';

    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = LayoutRepository::class;

    /**
     * @var array
     */
    public array $defaultColumns = ['id', 'name'];

    /**
     * @var string
     */
    public string $createViewRoute = 'lawoo.website.layouts.records.create';

    /**
     * @var string
     */
    public string $formViewRoute = 'lawoo.website.layouts.records.view';

    /**
     * Function boot
     */
    public function boot(): void
    {
        $this->title = __t('Layouts', 'Website');
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
        ];
    }
}
