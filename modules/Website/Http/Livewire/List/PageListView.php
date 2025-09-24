<?php

namespace Modules\Website\Http\Livewire\List;


use Modules\Web\Http\Livewire\List\BaseListView;
use Modules\Website\Models\Page;
use Modules\Website\Repositories\PageRepository;

class PageListView extends BaseListView
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
    public ?string $modelClass = 'Page';

    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = PageRepository::class;

    /**
     * @var array
     */
    public array $defaultColumns = ['id', 'name'];

    /**
     * @var string
     */
    public string $createViewRoute = 'lawoo.website.pages.records.create';

    /**
     * @var string
     */
    public string $formViewRoute = 'lawoo.website.pages.records.view';

    /**
     * Function boot
     */
    public function boot(): void
    {
        $this->title = __t('Pages', 'Website');
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
            'layout_id' => [
                'label' => __t('Layout', 'Website'),
            ],
            'is_active' => [
                'label' => __t('Active', 'Website'),
            ]
        ];
    }
}
