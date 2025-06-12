{{--
name: 'search_type_boolean',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'key',
    'checked',
    'label'
])

<flux:field variant="inline" class="mb-1">
    <flux:checkbox wire:model.live="panelFilters.{{ $key }}" :checked="$checked" />
    <flux:label>{{ $label }}</flux:label>
</flux:field>
