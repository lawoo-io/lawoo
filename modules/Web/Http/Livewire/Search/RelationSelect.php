<?php

namespace Modules\Web\Http\Livewire\Search;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class RelationSelect extends Component
{
    // --- Konfiguration (wird von außen übergeben)
    public string $filterKey;
    public array $filterConfig;

    #[Reactive]
    public $selected = null;

    public array $options = [];
    public $selection = null;
    public bool $multiple;
    public string $placeholder;
    public string $keyColumn;
    public string $displayColumn;
    public int $limit = 10;
    public int $totalCount = 0;
    public bool $hasMore = false;

    public function updatedSelected($value): void
    {
        $this->initialCurrentValues();

        $this->selection = $this->selected ? array_values($this->selected) : null;
    }

    public function mount($filterKey, $filterConfig)
    {
        $this->filterKey = $filterKey;
        $this->filterConfig = $filterConfig;
        $this->multiple = $this->filterConfig['multiple'] ?? false;
        $this->placeholder = $this->filterConfig['label'] ?? 'Select...';

        $this->keyColumn = $this->filterConfig['relation']['key_column'] ?? 'id';
        $this->displayColumn = $this->filterConfig['relation']['display_column'];

        if ($this->selected) {
            $this->initialCurrentValues();
            $this->selection = array_values($this->selected);
        } else {
            $this->selection = null;
        }

        $this->loadData();
    }

    public function initialCurrentValues(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $optionIds = array_keys($this->options);

        $missingIds = array_diff($this->selected, $optionIds);

        if (empty($missingIds)) {
            return;
        }

        $modelClass = $this->filterConfig['relation']['model'];

        $missingOptions = $modelClass::query()
            ->whereIn($this->keyColumn, $missingIds)
            ->pluck($this->displayColumn, $this->keyColumn)
            ->toArray();

        $this->options = $this->options + $missingOptions;
    }

    public function loadData(): void
    {
        $modelClass = $this->filterConfig['relation']['model'];
        $modelInstance = new $modelClass();
        $cacheTags = [$modelInstance->getCacheTag()];

        $modelSlug = str_replace('\\', '_', $modelClass);
        $cacheKey = "livewire.data-loader.{$modelSlug}.{$this->displayColumn}.limit-{$this->limit}";

        $cachedData = Cache::tags($cacheTags)->remember($cacheKey, now()->addDay(), function () use ($modelClass) {
            $query = $modelClass::query()->where('is_active', true);

            $totalCount = $query->clone()->count();

            $options = $query->limit($this->limit)
                ->pluck($this->displayColumn, $this->keyColumn)
                ->toArray();

            return [
                'totalCount' => $totalCount,
                'options' => $options,
            ];
        });

        $this->totalCount = $cachedData['totalCount'];
        $this->options = $cachedData['options'];
        $this->hasMore = ($this->totalCount > $this->limit);
    }

    public function search(string $searchTerm = '')
    {
        $modelClass = $this->filterConfig['relation']['model'];
        $modelInstance = new $modelClass();

        $cacheTags = [$modelInstance->getCacheTag()];

        $modelSlug = str_replace('\\', '_', $modelClass);
        $searchTermSlug = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $searchTerm));

        $cacheKey = "livewire:search:{$modelSlug}:{$this->displayColumn}:q:{$searchTermSlug}:limit-{$this->limit}";

        $searchResults = Cache::tags($cacheTags)
            ->remember($cacheKey, now()->addMinutes(10), function () use ($modelClass, $searchTerm) {
                return $modelClass::query()
                    ->where($this->displayColumn, 'ilike', '%' . $searchTerm . '%')
                    ->limit($this->limit)
                    ->pluck($this->displayColumn, $this->keyColumn)
                    ->toArray();
            });
        $this->options = $searchResults + $this->options;
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

    #[On('set-relation-value')]
    public function setValue(string $filterKey, $value): void
    {
        if ($this->filterKey !== $filterKey || !$value) {
            return;
        }

        $this->selected = $value;
        $this->initialCurrentValues();
        $this->selection = array_values($this->selected);
    }

    public function render()
    {
        return view('livewire.web.search.relation-select');
    }
}
