<?php

namespace Modules\Web\Http\Livewire\List;

use Modules\Web\Repositories\RoleRepository;

class RoleListView extends BaseListView
{
    public ?string $moduleName = 'Web';
    public ?string $modelClass = 'Role';

    protected string $repositoryClass = RoleRepository::class;

    public array $defaultColumns = ['name', 'description'];

    public bool $checkboxes = true;

    public string $formViewRoute = 'lawoo.settings.roles.records.view';

    public function boot(): void
    {
        $this->title = __t('Roles', 'Web');
        parent::boot();
    }

    public static function setSearchFields(): array
    {
        return [
            'name' => __t('Name', 'Web'),
            'description' => __t('Description', 'Web'),
            'module' => __t('Module', 'Web'),
        ];
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
            'description' => [
                'label' => __t('Description', 'Web'),
            ],
            'module' => [
                'label' => __t('Module', 'Web'),
            ],
            'is_system' => [
                'label' => __t('System', 'Web'),
                'type' => 'switch',
            ],
        ];
    }
}
