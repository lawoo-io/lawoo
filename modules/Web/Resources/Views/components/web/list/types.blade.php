{{--
name: 'web_list_types',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'type',
    'value'
])

@if ($type === 'switch')
    <x-web.list.types.switch :value="$value"/>
@endif
