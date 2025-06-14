{{--
name: 'search_type_select',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'key',
    'label',
    'options'
])

<flux:select
    wire:model.live="panelFilters.{{ $key }}"
    variant="listbox"
    size="xs"
    class="mb-1"
>
    <flux:select.option class="size-7" value="">-- {{ $label }} --</flux:select.option>
    @foreach($options as $optionValue => $optionLabel)
        <flux:select.option class="size-7" :value="$optionValue">{{ $optionLabel }}</flux:select.option>
    @endforeach
</flux:select>
