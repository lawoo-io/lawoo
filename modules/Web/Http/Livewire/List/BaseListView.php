<?php

namespace Modules\Web\Http\Livewire\List;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BaseListView extends Component
{
    use WithPagination;

    /**
     * Component configuration
     */
    public string $title = 'List View';
    public ?string $moduleName = null;
    public ?string $modelClass = null;
    protected $repository = null;
    public string $view = 'livewire.web.list.base-list-view';

    /**
     * Key field for Livewire
     */
    public string $keyField = 'id';

    /**
     * Checkboxes
     */
    public bool $checkboxes = true;

    /**
     * Optionale fields
     */
    public array $visibleColumns = [];
    public array $defaultColumns = [];
    public array $availableColumns = [];

    /**
     * Sortable columns
     */
    public array $sortColumns = ['id'];

    /**
     * Search configuration
     */
    public bool $showSearch = true;

    /**
     * Searchable Fields
     */
    public array $searchFields = [];

    /**
     * Filter configuration
     */
    #[Url(keep: true)]
    public array $filters = [];

    #[Url]
    public string $sortBy = 'id';

    #[Url]
    public string $sortDirection = 'asc';

    /**
     * Pagination
     */
    public int $perPage = 15;
    public bool $hasMorePages = false;

    /**
     * Select configuration
     */
    public array $selected = [];
    public bool $selectAll = false;

    public int $selectedAllRecords = 0;

    /**
     * Delete configuration
     */
    public array $excludedIds = [];

    /**
     * Query
     */
    public $query = null;

    // Loading States
    public bool $isLoading = true;

    public function boot(): void
    {
        $this->searchFields = ['id' => __t('ID', 'Web')];
    }

    public function mount(): void {
        $this->moduleName = $this->moduleName ?? $this->guessModuleName();

        // Visible Coluns Session
        $cookieKey = 'table_columns_' . $this->moduleName . '_' . $this->modelClass;
        $cookieValue = request()->cookie($cookieKey);

        $this->visibleColumns = $cookieValue
            ? json_decode($cookieValue, true)
            : $this->defaultColumns;

        $this->availableColumns = $this->getAvailableColumns();

        // Per page
        $cookieKey = 'per_page_' . $this->moduleName . '_' . $this->modelClass;
        $cookieValue = request()->cookie($cookieKey);
        $this->perPage = $cookieValue ?? $this->perPage;
    }

    public function updatedPerPage($value): void
    {
        $this->changePerPage($value);
        $this->clearSelection();
    }

    public function changePerPage($value): void
    {
        $value = (int)$value; // Cast zu int (null wird zu 0)

        if($value < 1) $value = 1;
        if($value > 200) $value = 200;

        $this->perPage = $value;

        $cookieKey = 'per_page_' . $this->moduleName . '_' . $this->modelClass;
        cookie()->queue(cookie($cookieKey, $value, 60 * 24 * config('app.cookie_settings_days')));
    }

    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'label' => __t('ID', 'Web'),
            ],
        ];
    }

    public function toggleColumn(string $column): void
    {
        if (in_array($column, $this->visibleColumns)) {
            $this->visibleColumns = array_diff($this->visibleColumns, [$column]);
        } else {
            $this->visibleColumns[] = $column;
        }

        // Set cookies
        $cookieKey = 'table_columns_' . $this->moduleName . '_' . $this->modelClass;
        cookie()->queue($cookieKey, json_encode($this->visibleColumns), 60 * 24 * config('app.cookie_settings_days'));
    }

    public function isVisible(string $column): bool
    {
        return in_array($column, $this->visibleColumns);
    }

    public function loadData(): Builder|null
    {
        $repository = $this->resolveRepository();

        if (!$repository) {
            $this->isLoading = false;
            return null;
        }

        $this->isLoading = true;

        try {
            $selectFields = $this->getSelectFields();

            return $repository->getFilteredData([
                'filters' => $this->filters,
                'search_fields' => $this->searchFields, // <- Übergabe an Repository
                'sort' => [$this->sortBy, $this->sortDirection],
                'select' => $selectFields,
            ]);

        } catch (\Exception $e) {
            session()->flash('error', 'Fehler beim Laden der Daten: ' . $e->getMessage());
            return null;
        } finally {
            $this->isLoading = false;
        }
    }

    protected function getSelectFields(): array
    {
        $baseFields = ['id'];

        $visibleFields = $this->visibleColumns;

        return array_unique(array_merge($baseFields, $visibleFields));
    }

    protected function resolveRepository()
    {
        if (!$this->modelClass || !$this->moduleName) {
            return null;
        }

        $repositoryClass = "Modules\\{$this->moduleName}\\Repositories\\{$this->modelClass}Repository";

        if (class_exists($repositoryClass)) {
            return new $repositoryClass();
        }

        return null;
    }

    protected function guessModuleName(): ?string
    {
        // Versuche Modulname aus Component-Namespace zu ermitteln
        $reflection = new \ReflectionClass($this);
        $namespace = $reflection->getNamespaceName();

        if (preg_match('/Modules\\\\([^\\\\]+)/', $namespace, $matches)) {
            return $matches[1];
        }

        return null;
    }

    #[On('filters-updated')]
    public function updateFilters(array $filters): void
    {
        $this->filters = $filters;
        $this->resetPage();
    }

    public function sort(string $sortBy, ?string $direction = null): void
    {
        if ($this->sortBy === $sortBy) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $sortBy;
            $this->sortDirection = $direction ?? 'asc';
        }
    }

    #[On('list-refresh')]
    public function refresh(): void
    {
        $this->clearSelection();
    }

    // Self-dispatching methods for internal communication
    public function dispatchSearchUpdate(): void
    {
        $this->dispatch('search-updated', query: $this->search)->self();
    }

    public function dispatchFilterUpdate(): void
    {
        $this->dispatch('filter-changed', filters: $this->filters)->self();
    }

    // Search with debouncing - ONLY from external events
    public function updatedSearch(): void
    {
        // Only triggered by external search component events
        $this->resetPage();
    }

    // Sorting helper
    public function sortBy(string $field): void
    {
        $this->sort($field);
    }

    // Helper for checking if field is sorted
    public function isSorted(string $field): bool
    {
        return $this->sortBy === $field;
    }

    // Bulk Actions
    public array $bulkActions = [];

    public function toggleSelectAll()
    {
        $this->hasMorePages = false;
        $this->selectedAllRecords = 0;
        if ($this->getSelectedCount() > 0) {
            $this->selected = [];
        } else {
            // Hole alle IDs der aktuellen Seite
            $data = $this->loadData();
            if ($data) {
                $paginated = $data->paginate($this->perPage);
                if($paginated->total() > $paginated->count()) {
                    $this->hasMorePages = true;
                }
                $this->selected = $paginated->pluck('id')->map(fn($id) => (string) $id)->toArray();
            }
        }
    }

    public function selectAllRecords(): void
    {
        $this->selectedAllRecords = $this->loadData()->paginate($this->perPage)->total();
    }

    public function updatedPaginators(): void
    {
        $this->clearSelection();
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
        $this->selectedAllRecords = 0;
    }

    public function getSelectedCount(): int
    {
        return count($this->selected);
    }

    public function delete(): void
    {
        $result = $this->resolveRepository()->delete($this->selected, $this->selectedAllRecords, $this->excludedIds);
    }

    public function render()
    {
        $query = $this->loadData();

        return view($this->view, [
            'data' => $query->simplePaginate($this->perPage),
        ]);
    }
}
