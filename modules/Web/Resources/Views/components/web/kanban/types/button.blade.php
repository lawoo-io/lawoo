{{--
name: 'kanban_type_button',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'value'
])
<flux:button :class="$field['class'] ?? false" :icon="$field['icon'] ?? false" :wire:click="$field['click'] ?? false">
    {{ $value }}
</flux:button>
