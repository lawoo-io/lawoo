<?php

namespace Modules\Website\Http\Livewire\List;


use Modules\Web\Http\Livewire\List\BaseListView;
use Modules\Website\Repositories\ThemeRepository;

class ThemeListView extends BaseListView
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
    public ?string $modelClass = 'Theme';

    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = ThemeRepository::class;

    /**
     * @var array
     */
    public array $defaultColumns = ['id', 'name'];

    /**
     * @var string
     */
    public string $createViewRoute = 'lawoo.website.themes.records.create';

    /**
     * @var string
     */
    public string $formViewRoute = 'lawoo.website.themes.records.view';

    /**
     * Function boot
     */
    public function boot(): void
    {
        $this->title = __t('Themes', 'Website');
        parent::boot();
    }

    /**
     * Function setSearchFields
     */
    public static function setSearchFields(): array
    {
        return [
            'name' => __t('Name', 'Web'),
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
            'name' => [
                'label' => __t('Name', 'Web'),
            ],
            'author_name' => [
                'label' => __t('Author', 'Website'),
            ]
        ];
    }
}
