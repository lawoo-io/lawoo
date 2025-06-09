<?php

namespace Modules\Web\Http\Livewire\Search;

use Livewire\Component;

class BaseSearch extends Component
{
    /**
     * Die durchsuchbaren Felder, die von der ListView übergeben werden.
     */
    public array $searchFields = [];

    public array $activeFilters = [];

    public array $availableFilters = [];

    /**
     * Der Live-Suchbegriff, den der Benutzer gerade tippt.
     */
    public string $search = '';

    /**
     * Der numerische Index des aktuell ausgewählten Eintrags im Dropdown.
     */
    public int $selectedIndex = -1;

    /**
     * Steuert die Sichtbarkeit des Dropdowns.
     */
    public bool $showDropdown = false;

    public function mount(array $searchFields = [], array $initialFilters = [], array $availableFilters = []): void
    {
        $this->searchFields = $searchFields;
        $this->activeFilters = $initialFilters;
        $this->availableFilters = $availableFilters;
    }

    public function updatedActiveFilters(): void
    {
        $this->activeFilters = array_filter($this->activeFilters, function ($value) {
            return $value !== '' && $value !== null;
        });
        $this->updateRecord();
    }

    public function addFilter(string $key): void
    {
        $searchValue = trim($this->search);
        if (empty($searchValue)) return;

        if (!isset($this->activeFilters[$key])) {
            $this->activeFilters[$key] = [];
        }

        if (!in_array($searchValue, $this->activeFilters[$key])) {
            $this->activeFilters[$key][] = $searchValue;
        }

        $this->resetSearch();
        $this->updateRecord();
    }

    public function updateRecord(): void
    {
        $this->dispatch('filters-updated', filters: $this->activeFilters);
    }

    public function removeFilterGroup(string $key): void
    {
        unset($this->activeFilters[$key]);
        $this->updateRecord();
    }

    public function removeFilter(string $key, int $valueIndex): void
    {
        if (isset($this->activeFilters[$key][$valueIndex])) {
            unset($this->activeFilters[$key][$valueIndex]);

            if (empty($this->activeFilters[$key])) {
                unset($this->activeFilters[$key]);
            }

            $this->updateRecord();
        }
    }

    /**
     * Wird ausgeführt, wenn der Benutzer im Suchfeld tippt.
     */
    public function updatedSearch()
    {
        $this->showDropdown = !empty($this->search);
        $this->selectedIndex = 0;
    }

    /**
     * Wählt den nächsten Eintrag in der Liste aus (Pfeiltaste nach unten).
     */
    public function incrementSelection()
    {
        $maxIndex = count($this->searchFields) - 1;
        if ($this->selectedIndex < $maxIndex) {
            $this->selectedIndex++;
        }
    }

    /**
     * Wählt den vorherigen Eintrag in der Liste aus (Pfeiltaste nach oben).
     */
    public function decrementSelection()
    {
        if ($this->selectedIndex > 0) {
            $this->selectedIndex--;
        }
    }

    /**
     * Wird durch die Enter-Taste ausgelöst.
     */
    public function selectHighlightedItem()
    {
        $keys = array_keys($this->searchFields);
        if (isset($keys[$this->selectedIndex])) {
            $key = $keys[$this->selectedIndex];
            $this->addFilter($key);
        }
    }

    /**
     * Setzt den Auswahlindex, wenn die Maus über ein Element fährt.
     */
    public function setSelection(int $index): void
    {
        $this->selectedIndex = $index;
    }

    /**
     * Setzt den Auswahlindex zurück, wenn die Maus das Dropdown verlässt.
     */
    public function resetSelection(): void
    {
        $this->selectedIndex = -1;
    }

    /**
     * @return void
     */
    public function handleBackspace(): void
    {
        if (empty($this->search) && !empty($this->activeFilters)) {

            $lastKey = array_key_last($this->activeFilters);

            $lastValueIndex = array_key_last($this->activeFilters[$lastKey]);

            $this->removeFilter($lastKey, $lastValueIndex);
        }
    }

    /**
     * Setzt die Suche zurück und schließt das Dropdown.
     */
    public function resetSearch()
    {
        $this->search = '';
        $this->showDropdown = false;
        $this->selectedIndex = -1;
    }

    public function render()
    {
        return view('livewire.web.search.base-search', [
            'activeFilters' => $this->activeFilters,
        ]);
    }
}
