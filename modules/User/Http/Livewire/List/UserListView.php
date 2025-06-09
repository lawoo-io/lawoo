<?php

namespace Modules\User\Http\Livewire\List;

use Flux\Flux;
use Modules\Web\Http\Livewire\List\BaseListView;

class UserListView extends BaseListView
{
    public string $title = 'Users';
    public ?string $moduleName = 'User';
//    public string $view = 'livewire.user.list.user-list-view';
    public ?string $modelClass = 'User';

    public array $sortColumns = ['id', 'is_active'];

    public array $searchFields = [];

    public int $perPage = 1;

    public array $defaultColumns = ['name', 'email'];

    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'label' => __t('ID', 'User')
            ],
            'name' => [
                'label' => __t('Name', 'User')
            ],
            'email' => [
                'label' => __t('Email', 'User')
            ],
            'is_active' => [
                'label' => __t('Active', 'User'),
                'type' => 'switch'
            ],
            'created_at' => [
                'label' => __t('Created At', 'User')
            ],
            'updated_at' => [
                'label' => __t('Updated At', 'User')
            ],
        ];
    }

    public function boot(): void
    {
        $this->title = __t('Users', 'User');
        $this->searchFields = ['name' => __t('Name', 'Web'), 'email' => __t('Email', 'Web')];

        $this->availableFilters = [
            // Column 1
            'account' => [
                'label' => __t('Filter', 'User'),
                'column' => 1,
                'filters' => [
                    'is_active' => [
                        'label' => __t('Active', 'User'),
                        'type' => 'boolean',
                    ],
                    'is_super_admin' => [
                        'label' => __t('Super Admin', 'User'),
                        'type' => 'boolean',
                    ],
                    'select_test' => [
                        'label' => __t('Test', 'User'),
                        'type' => 'select',
                        'options' => ['option1' => 'Option 1', 'option2' => 'Option 2'],
                    ]
                ]
            ],

            // Column 2
            'dates' => [
                'label' => __t('Date', 'User'),
                'column' => 2,
                'filters' => [
                    'created_at' => [
                        'label' => __t('Created At', 'User'),
                        'type' => 'date',
                    ],
                ]
            ],
        ];
    }

    public function delete(): void
    {
        $this->excludedIds = [auth()->id()];
        parent::delete();
    }

    public function sendMessage()
    {
        Flux::toast(text: 'sendMessage function', variant: 'success');
    }

    public function render()
    {
        $query = $this->loadData();

        $userList = view('livewire.user.list.user-list');

        return view($this->view, [
            'data' => $query->simplePaginate($this->perPage),
            'actions' => $userList->renderSections()['actions'] ?? '',
        ]);
    }

}
