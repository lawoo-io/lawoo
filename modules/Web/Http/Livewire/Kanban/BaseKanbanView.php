<?php

namespace Modules\Web\Http\Livewire\Kanban;


use Flux\Flux;
use Livewire\Component;
use Modules\Web\Http\Livewire\List\BaseListView;

class BaseKanbanView extends BaseListView
{
    /**
     * @var string View
     */
    public string $view = 'livewire.web.kanban.base-kanban-view';

    /**
     * @var string
     */
    public string $type = 'default';

    /**
     * @var array
     */
    public array $availableColumns = [];

    public array $availableOptions = [];

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->title = __t('Kanban View', 'Web');
        $this->availableColumns = $this->getAvailableColumns();
        $this->availableOptions = $this->getAvailableOptions();
    }

    public function getAvailableColumns(): array
    {
        return [];
    }

    public function getAvailableOptions(): array
    {
        return [];
    }

    public function deleteItem($id): void
    {
        $ids[] = $id;
        $this->resolveRepository()->delete($ids);
    }

}
