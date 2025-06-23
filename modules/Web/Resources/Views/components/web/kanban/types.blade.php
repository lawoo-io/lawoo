{{--
name: 'kanban_types',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'value'
])

@if ($field['type'] === 'heading')
    <x-web.kanban.types.heading :field="$field" :value="$value"/>
@endif
