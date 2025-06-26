<?php

namespace Modules\User\Http\Livewire\List;

use Flux\Flux;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Modules\Web\Http\Livewire\List\BaseListView;

class UserListView extends BaseListView
{
    public string $title = 'Users';
    public ?string $moduleName = 'User';
    public ?string $modelClass = 'User';

    public array $sortColumns = ['id', 'is_active'];

    public int $perPage = 100;

    public array $defaultColumns = ['name', 'email'];

    public bool $cacheEnabled = true;

    public array $cacheTags = ['table:users'];

    public array $availableFilters = [];

    public string $formViewRoute = 'lawoo.users.records.view';

    public bool $showSearch = true;

    public bool $checkboxes = true;


    public function boot(): void
    {
        $this->title = __t('Users', 'User');
        parent::boot();
    }


    public static function setSearchFields(): array
    {
        return [
            'name' => __t('Name', 'User'),
            'email' => __t('Email', 'User')
        ];
    }

    public static function setAvailableFilters(): array
    {
        return  [
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
//                    'select_test' => [
//                        'label' => __t('Test', 'User'),
//                        'type' => 'select',
//                        'options' => ['option1' => 'Option 1', 'option2' => 'Option 2'],
//                    ]
                ]
            ],

            'language' => [
                'label' => __t('Language', 'User'),
                'column' => 2,
                'filters' => [
                    'language_id' => [
                        'label' => __t('Language', 'User'),
                        'type' => 'relation',
                        'relation' => [
                            'model' => \Modules\Core\Models\Language::class,
                            'key_column' => 'id',
                            'display_column' => 'name',
                        ],
                        'multiple' => true,
                        'operator' => 'whereIn',
                    ],
                    'created_at' => [
                        'label' => __t('Created At', 'User'),
                        'type' => 'datepicker',
                        'mode' => 'range',
                        'presets' => 'today yesterday thisWeek last7Days thisMonth yearToDate',
                        'operator' => 'date_between',
                        'formats' => [
                            'en' => 'm/d/Y',
                            'de' => 'd.m.Y',
                        ]
                    ],
//                    'language_id' => [
//                        'label' => __t('Language', 'User'),
//                        'type' => 'select',
//                        'options' => [1 => 'Option 1', 'option2' => 'Option 2'],
//                    ]
                ]
            ],

            // Column 2
//            'dates' => [
//                'label' => __t('Created', 'User'),
//                'column' => 2,
//                'filters' => [
//                    'created_at' => [
//                        'label' => __t('Created At', 'User'),
//                        'type' => 'datepicker',
//                        'mode' => 'range',
//                        'presets' => 'today yesterday thisWeek last7Days thisMonth yearToDate',
//                        'operator' => 'date_between',
//                        'formats' => [
//                            'en' => 'm/d/Y',
//                            'de' => 'd.m.Y',
//                        ]
//                    ],
//                ]
//            ],
        ];
    }

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
                'label' => __t('Email', 'User'),
                'clicked' => true,
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
        $data = $this->prepareData();
        if (!$data) {
            return view($this->view, ['data' => new Paginator([], $this->perPage)]);
        }

        $userList = view('livewire.user.list.user-list');

        View::share('livewireComponent', $this);

        return view($this->view, [
            'data' => $data,
            'actions' => $userList->renderSections()['actions'] ?? '',
            'viewButtons' => $userList->renderSections()['viewButtons'] ?? '',
        ]);
    }

}
