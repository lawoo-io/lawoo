{{--
name: 'kanban_type_heading',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'value'
])

<flux:heading :class="$field['class'] ?? false" :level="$field['level'] ?? 1" :icon="$field['icon'] ?? false">
    {{ $value }}
</flux:heading>
