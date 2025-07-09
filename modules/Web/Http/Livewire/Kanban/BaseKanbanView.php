<?php

namespace Modules\Web\Http\Livewire\Kanban;


use Flux\Flux;
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

    /**
     * @var array
     */
    public array $availableOptions = [];

    /**
     * @var array
     */
    public array $availableButtons = [];

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->title = __t('Kanban View', 'Web');
        $this->availableColumns = $this->getAvailableColumns();
        $this->availableOptions = $this->getAvailableOptions();
        $this->availableButtons = $this->getAvailableButtons();
        $this->searchFields = $this->setSearchFields();
        $this->availableFilters = $this->setAvailableFilters();
    }

    public static function setSearchFields(): array
    {
        return [];
    }

    public static function setAvailableFilters(): array
    {
        return [];
    }

    public function getAvailableColumns(): array
    {
        return [];
    }

    public function getAvailableOptions(): array
    {
        return [];
    }

    public function getAvailableButtons(): array
    {
        return [];
    }

    public function setModalContent(int $id, array $params): void
    {
        $this->modalContent = '';
    }

    public function openModal(int $id = 0, array $params = []): void
    {
        $this->setModalContent($id, $params);
        Flux::modal('modal-view')->show();
    }

    public function closeModal(): void
    {
        Flux::modal('modal-view')->close();
        $this->reset(['modalContent']);
    }

    public function deleteItem($id): void
    {
        $ids[] = $id;
        $this->resolveRepository()->delete($ids);
    }

}
