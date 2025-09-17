{{--
name: 'web_form_types',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'options'
])
@if ($options['type'] === 'input')
    <x-web.form.types.input :field="$field" :options="$options"/>
@elseif($options['type'] === 'switch')
    <x-web.form.types.switch :field="$field" :options="$options"/>
@elseif($options['type'] === 'checkbox')
    <x-web.form.types.checkbox :field="$field" :options="$options"/>
@elseif($options['type'] === 'checkbox_group')
    <x-web.form.types.checkbox-group :field="$field" :options="$options"/>
@elseif($options['type'] === 'select')
    <x-web.form.types.select :field="$field" :options="$options" />
@elseif($options['type'] === 'fileUploader')
    <x-web.form.types.file-uploader :field="$field" :options="$options"/>
@elseif($options['type'] === 'cards')
    <x-web.form.types.cards :field="$field" :options="$options"/>
@elseif($options['type'] === 'textarea')
    <x-web.form.types.textarea :field="$field" :options="$options"/>
@elseif($options['type'] === 'editor')
    <x-web.form.types.editor :field="$field" :options="$options"/>
@endif
