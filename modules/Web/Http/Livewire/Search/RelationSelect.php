<?php

namespace Modules\Web\Http\Livewire\Search;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class RelationSelect extends Component
{
    // --- Konfiguration (wird von außen übergeben)
    public string $filterKey;
    public array $filterConfig;

    // --- Interne Eigenschaften
    public array $options = [];
    public $selection = null;
    public bool $multiple;
    public string $placeholder;
    public string $keyColumn;
    public string $displayColumn;
    public int $limit = 10;
    public int $totalCount = 0;
    public bool $hasMore = false;

    public function mount($filterKey, $filterConfig, $currentValue = null)
    {
        $this->filterKey = $filterKey;
        $this->filterConfig = $filterConfig;
        $this->multiple = $this->filterConfig['multiple'] ?? false;
        $this->placeholder = $this->filterConfig['label'] ?? 'Select...';

        $this->keyColumn = $this->filterConfig['relation']['key_column'] ?? 'id';
        $this->displayColumn = $this->filterConfig['relation']['display_column'];

        if ($currentValue) {
            $this->selection = array_values($currentValue);
        } else {
            $this->selection = null;
        }

        $this->loadData();
    }

    public function loadData(): void
    {
        $modelClass = $this->filterConfig['relation']['model'];

        $totalCountQuery = $modelClass::query()
            ->where('is_active', true);

        $this->totalCount = $totalCountQuery->count();
        $this->hasMore = ($this->totalCount > $this->limit);

        $this->options = $totalCountQuery->limit($this->limit)
            ->pluck($this->displayColumn, $this->keyColumn)
            ->toArray();
    }

    public function search(string $searchTerm = '')
    {
        $modelClass = $this->filterConfig['relation']['model'];

        $this->options = $modelClass::query()
            ->where($this->displayColumn, 'ilike', '%' . $searchTerm . '%')
            ->limit($this->limit)
            ->pluck($this->displayColumn, $this->keyColumn)
            ->toArray();
    }

    public function updatedSelection(): void
    {
        $values = [];

        if (is_array($this->selection)) {
            foreach ($this->selection as $id) {
                $values[$this->options[$id]] = $id;
            }
        } elseif($this->selection) {
            $values[$this->options[$this->selection]] = $this->selection;
        }

        $this->dispatch('update-panel-filter',
            key: $this->filterKey,
            value: $values,
        );
    }

    #[On('reset-relation-select')]
    public function resetSelectionIfKeyMatches(string $filterKey): void
    {
        if ($this->filterKey === $filterKey) {
            $this->selection = $this->multiple ? [] : null;
        }
    }

    public function render()
    {
        return view('livewire.web.search.relation-select');
    }
}
