{{--
name: 'form_type_textarea',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'options'
])
<div class="{{ $options['class'] }}">
    <flux:textarea
        wire:model="data.{{ $field }}"
        :rows="$options['rows'] ?? 'auto'"
        :label="$options['label'] ?? false"
        :placeholder="$options['placeholder'] ?? false"
    >

    </flux:textarea>
</div>
