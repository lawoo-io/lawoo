<?php

namespace Modules\Web\Http\Livewire\Form;

use Illuminate\Database\Eloquent\Model;
use Modules\Web\Repositories\RoleRepository;

class RoleFormView extends BaseFormView
{
    protected string $repositoryClass = RoleRepository::class;

    public string $permissionForEdit = 'web.settings.roles_permissions.edit';

    public string $permissionForDelete = 'web.settings.roles.delete';

    public string $recordsRoute = 'lawoo.settings.roles_records';

    public function setFields(): void
    {
        $this->fields = [
            'name' => [
                'type' => 'input',
                'label' => __t('Name', 'Web'),
                'class' => 'lg:col-span-6'
            ],
            'slug' => [
                'type' => 'input',
                'label' => __t('Slug', 'Web'),
                'class' => 'lg:col-span-6',
                'disabled' => true,
            ],
            'description' => [
                'type' => 'input',
                'label' => __t('Description', 'Web'),
                'class' => 'lg:col-span-6'
            ],
            'is_system' => [
                'type' => 'switch',
                'label' => __t('System', 'Web'),
                'disabled' => true,
                'class' => 'lg:col-span-6'
            ],
            'permissions' => [
                'type' => 'checkbox_group',
                'label' => __t('Permissions', 'Web'),
                'class' => 'lg:col-span-12 flex flex-wrap gap-4',
                'group_class' => 'flex-auto',
                'options' => $this->permissionOptions(),
            ]
        ];
    }

    public function setRules(): void
    {
        $this->rules = [
            'data.name' => 'required|string|min:3|max:100',
        ];
    }

    protected function update(): ?Model
    {
        $model = parent::update();
        if($model) {
            $model->permissions()->sync($this->data['permissions']);
        }


        return $model;
    }

    protected function loadData(): void
    {
        $record = $this->resolveRepository()->find($this->id);
        $this->data = $record->attributesToArray();
        $this->data['permissions'] = $record->permissions->sortBy('slug')->pluck('id')->toArray();
    }

    protected function permissionOptions(): array
    {
        $lists = $this->resolveRepository()->permissionOptions();
        return $lists;
    }
}
