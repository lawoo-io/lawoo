{{--
name: 'kanban_type_text',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'value'
])
<flux:text class="{{ $field['class'] }}">{{ $value }}</flux:text>
