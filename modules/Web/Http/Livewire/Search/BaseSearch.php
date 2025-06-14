<?php

namespace Modules\Web\Http\Livewire\Search;

use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class BaseSearch extends Component
{
    /**
     * Die verfügbaren Felder für die Text-Suche (z.B. ['name' => 'Name']).
     */
    public array $searchFields = [];

    /**
     * Die Definitionen für die interaktiven Panel-Filter.
     */
    public array $availableFilters = [];

    /**
     * Speichert die aktiven Text-Suchen (z.B. ['name' => ['Admin', 'User']]).
     */
    public array $searchFilters = [];

    /**
     * Speichert die aktiven Panel-Filter (z.B. ['is_active' => true]).
     */
    public array $panelFilters = [];

    /**
     * Der Live-Suchbegriff, den der Benutzer gerade tippt.
     */
    public string $search = '';

    /**
     * Der numerische Index des aktuell ausgewählten Eintrags im Dropdown.
     */
    public int $selectedIndex = -1;

    /**
     * Steuert die Sichtbarkeit des Such-Vorschlags-Dropdowns.
     */
    public bool $showDropdown = false;

    public array $flatAvailableFilters = [];


    /**
     * @param array $searchFields
     * @param array $availableFilters
     * @param array $searchFilters
     * @param array $panelFilters
     * @return void
     */
    public function mount(
        array $searchFields = [],
        array $availableFilters = [],
        array $searchFilters = [],
        array $panelFilters = []
    ): void {
        $this->searchFields = $searchFields;
        $this->availableFilters = $availableFilters;

        // Nimmt die Werte aus der URL beim ersten Laden entgegen
        $this->searchFilters = $searchFilters;
        $this->panelFilters = $panelFilters;

        $this->flattenAvailableFilters();
    }

    /**
     * @return void
     */
    private function flattenAvailableFilters(): void
    {
        $this->flatAvailableFilters = [];
        foreach ($this->availableFilters as $group) {
            if (!empty($group['filters'])) {
                // array_merge ist perfekt, um die Filter aus allen Gruppen zusammenzuführen
                $this->flatAvailableFilters = array_merge($this->flatAvailableFilters, $group['filters']);
            }
        }

    }

    /**
     * Wird immer aufgerufen, wenn sich $panelFilters durch eine Benutzer-Interaktion
     * im Panel ändert (dank wire:model.live).
     */
    public function updatedPanelFilters(): void
    {
        $this->panelFilters = array_filter($this->panelFilters, function ($value) {
            return $value !== '' && $value !== null && $value !== false;
        });

        $this->updateRecord();
    }

    /**
     * @param string $key
     * @return void
     */
    public function addSearchFilter(string $key): void
    {
        $searchValue = trim($this->search);
        if (empty($searchValue)) {
            return;
        }

        if (!isset($this->searchFilters[$key])) {
            $this->searchFilters[$key] = [];
        }

        if (!in_array($searchValue, $this->searchFilters[$key])) {
            $this->searchFilters[$key][] = $searchValue;
        }

        $this->resetSearch();
        $this->updateRecord();
    }

    /**
     * @param string $key
     * @return void
     */
    public function removeSearchFilterGroup(string $key): void
    {
        unset($this->searchFilters[$key]);
        $this->updateRecord();
    }

    /**
     * @param string $key
     * @param int $valueIndex
     * @return void
     */
    public function removeSearchFilterValue(string $key, int $valueIndex): void
    {
        if (isset($this->searchFilters[$key][$valueIndex])) {
            unset($this->searchFilters[$key][$valueIndex]);
            if (empty($this->searchFilters[$key])) {
                unset($this->searchFilters[$key]);
            }
            $this->updateRecord();
        }
    }

    public function removePanelFilterValue(string $key, int $valueIndex): void
    {
        if (isset($this->panelFilters[$key][$valueIndex])) {
            unset($this->panelFilters[$key][$valueIndex]);
            if (empty($this->panelFilters[$key])) {
                unset($this->panelFilters[$key]);
            }
            $this->updateRecord();
        }
    }

    /**
     * @return void
     */
    public function handleBackspace(): void
    {
        if (empty($this->search) && !empty($this->searchFilters)) {
            $lastKey = array_key_last($this->searchFilters);
            $lastValueIndex = array_key_last($this->searchFilters[$lastKey]);
            $this->removeSearchFilterValue($lastKey, $lastValueIndex);
        } elseif(empty($this->search) && !empty($this->panelFilters)) {
            $lastKey = array_key_last($this->panelFilters);
            $this->removePanelFilter($lastKey);
        }
    }


    /**
     * @param string $key
     * @return void
     */
    public function removePanelFilter(string $key)
    {
        unset($this->panelFilters[$key]);
        $this->updateRecord();
        $this->dispatch('reset-relation-select', filterKey: $key);
    }


    /**
     * Sendet den kompletten, aktuellen Zustand beider Filter-Arrays an die ListView.
     */
    public function updateRecord(): void
    {
        $this->dispatch('filters-updated',
            searchFilters: $this->searchFilters,
            panelFilters: $this->panelFilters
        );
    }


    /**
     * @return void
     */
    public function updatedSearch()
    {
        $this->showDropdown = !empty($this->search);
        $this->selectedIndex = 0;
    }

    /**
     * @return void
     */
    public function selectHighlightedItem()
    {
        if (!empty($this->search) && isset(array_keys($this->searchFields)[$this->selectedIndex])) {
            $key = array_keys($this->searchFields)[$this->selectedIndex];
            $this->addSearchFilter($key);
        }
    }

    /**
     * @return void
     */
    public function incrementSelection()
    {
        $maxIndex = count($this->searchFields) - 1;
        if ($this->selectedIndex < $maxIndex) {
            $this->selectedIndex++;
        }
    }

    /**
     * @return void
     */
    public function decrementSelection()
    {
        if ($this->selectedIndex > 0) {
            $this->selectedIndex--;
        }
    }

    /**
     * @param int $index
     * @return void
     */
    public function setSelection(int $index): void
    {
        $this->selectedIndex = $index;
    }

    /**
     * @return void
     */
    public function resetSelection(): void
    {
        $this->selectedIndex = -1;
    }

    /**
     * @return void
     */
    public function resetSearch()
    {
        $this->search = '';
        $this->showDropdown = false;
        $this->selectedIndex = -1;
    }

    #[On('update-panel-filter')]
    public function updatePanelFilter(string $key, $value): void
    {
        unset($this->panelFilters[$key]);

        if ($value !== '' && $value !== null && $value !== false) {
            if (isset($this->flatAvailableFilters[$key]) && $this->flatAvailableFilters[$key]['type'] === 'relation') {
                $this->panelFilters[$key] = $value;
            } else {
                $this->panelFilters[$key] = array_keys($value);
            }
        }

        if (empty($this->panelFilters[$key])) {
            unset($this->panelFilters[$key]);
        }

        $this->updateRecord();
    }

    #[On('apply-saved-filter')]
    public function applySavedFilter(array $filters): void
    {
        $this->searchFilters = $filters['searchFilters'] ?? [];
        $this->panelFilters = $filters['panelFilters'] ?? [];

        foreach ($this->flatAvailableFilters as $key => $filter) {
            if ($filter['type'] === 'relation') {
                $valueToDispatch = $this->panelFilters[$key] ?? null;
                $this->dispatch('set-relation-value',
                    filterKey: $key,
                    value: $valueToDispatch
                );
            }
        }

        $this->updateRecord();
    }

    /**
     * Render
     */
    public function render()
    {
        return view('livewire.web.search.base-search');
    }
}
