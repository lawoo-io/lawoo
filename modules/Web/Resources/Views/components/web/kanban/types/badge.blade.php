{{--
name: 'kanban_type_badge',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'value'
])

<flux:badge :class="$field['class'] ?? false" :color="$field['color'] ?? false">{{ $value }}</flux:badge>
