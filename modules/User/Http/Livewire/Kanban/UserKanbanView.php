<?php

namespace Modules\User\Http\Livewire\Kanban;


use Flux\Flux;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Modules\Core\Models\UserExtended;
use Modules\User\Http\Livewire\List\UserListView;
use Modules\User\Repositories\UserRepository;
use Modules\Web\Http\Livewire\Kanban\BaseKanbanView;

class UserKanbanView extends BaseKanbanView
{
    public ?string $moduleName = 'User';
    public ?string $modelClass = 'User';
    protected string $repositoryClass = UserRepository::class;
    public array $availableFilters = [];
    public string $formViewRoute = 'lawoo.users.records.view';

    public function boot(): void
    {
        parent::boot();
        $userListView = app(UserListView::class);
        $this->searchFields = $userListView->setSearchFields();
        $this->availableFilters = $userListView->setAvailableFilters();
        $this->title = __t('Users', 'User');
    }

    public function getAvailableOptions(): array
    {
        return [
            'deleteItem' => [
                'label' => __t('Delete', 'User'),
                'variant' => 'danger',
                'click' => true,
            ]
        ];
    }

    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'visible' => false,
            ],
            'name' => [
                'visible' => true,
                'type' => 'heading',
                'lever' => 2,
                'class' => 'col-span-6',
            ],
        ];
    }

    public function deleteItem($id): void
    {
        if($id === auth()->id()) {
            Flux::toast(text: __t("You can't delete yourself!", "User"), variant: 'danger');
            return;
        }

        // Check SuperAdmin
        $superAdmin = UserExtended::where('id', $id)->where('is_super_admin', true)->first();
        if($superAdmin && !auth()->user()->isSuperAdmin()){
            Flux::toast(text: __t("You can't delete Super Admins!", "User"), variant: 'danger');
            return;
        }

        parent::deleteItem($id);
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
