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
@elseif($field['type'] === 'text')
    <x-web.kanban.types.text :field="$field" :value="$value"/>
@elseif($field['type'] === 'button')
    <x-web.kanban.types.button :field="$field" :value="$value"/>
@elseif($field['type'] === 'badge')
    <x-web.kanban.types.badge :field="$field" :value="$value"/>
@endif
