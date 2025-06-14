{{--
name: 'search_type_datepicker',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'key',
    'label',
    'mode',
    'presets'
])
<flux:date-picker wire:model.model.live="panelFilters.{{$key}}"
                  :placeholder="$label"
                  :mode="$mode"
                  :presets="$presets"
                  locale="{{ auth()->user()->language->code }}"
                  size="sm"
                  class="mb-1"
/>
