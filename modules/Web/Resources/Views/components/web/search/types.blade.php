{{--
name: 'search_types',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}

@props([
    'key',
    'filter',
    'panelFilters'
])

@if ($filter['type'] === 'boolean')
    <x-web.search.types.boolean :key="$key" :checked="isset($panelFilters[$key]) && $panelFilters[$key]" :label="$filter['label']" />
@elseif($filter['type'] === 'select')
    <x-web.search.types.select :key="$key" :label="$filter['label']" :options="$filter['options']"/>
@elseif($filter['type'] === 'datepicker')
    <x-web.search.types.datepicker :key="$key"
                                   :label="$filter['label']"
                                   :mode="$filter['mode']"
                                   :presets="isset($filter['presets']) ? $filter['presets'] : false"
    />
@elseif($filter['type'] === 'relation')
    @livewire('web.search.relation-select', [
            'filterKey' => $key,
            'filterConfig' => $filter,
            'selected' => $panelFilters[$key] ?? null,
            'placeholder' => $filter['label'],
            'lazy' => false,
        ], key('relation-filter-' . $key))
@endif
