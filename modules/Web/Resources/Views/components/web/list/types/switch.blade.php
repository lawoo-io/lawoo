{{--
name: 'web_lists_switch',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'value',
])
<flux:switch :checked="$value"  disabled=""/>
