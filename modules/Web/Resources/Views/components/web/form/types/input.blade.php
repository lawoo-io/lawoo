{{--
name: 'form_type_input',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'options'
])
<flux:field class="{{ $options['class'] }}">
    @if (isset($options['label']) && $options['label'])
        <flux:label wire:dirty.class="!text-yellow-500" wire:target="data.{{ $field }}">{{ $options['label'] }} @if(in_array('required', $options) && $options['required'])<span class="ml-1 text-red-600">*</span>@endif</flux:label>
    @endif
    @if (isset($options['description_top']))
        <flux:description>{{ $options['description_top'] }}</flux:description>
    @endif

    @if(isset($options['live']) && $options['live'] === true)
        <flux:input
            wire:model.live="data.{{ $field }}"
            :wire:blur="$options['blur'] ?? false"
            :disabled="isset($options['disabled']) && $options['disabled'] ?? false"
            :required="isset($options['required']) && $options['required'] ?? false"
            :placeholder="isset($options['placeholder']) ? $options['placeholder'] : false"
        >
            <x-web.form.helpers.translatable-icon-trailing :field="$field"/>
        </flux:input>
    @else
        <flux:input
            wire:model="data.{{ $field }}"
            :wire:blur="$options['blur'] ?? false"
            :disabled="isset($options['disabled']) && $options['disabled'] ?? false"
            :required="isset($options['required']) && $options['required'] ?? false"
            :placeholder="isset($options['placeholder']) ? $options['placeholder'] : false"
        >
            <x-web.form.helpers.translatable-icon-trailing :field="$field"/>
        </flux:input>
    @endif
    @if (isset($options['description_bottom']))
        <flux:description>{{ $options['description_bottom'] }}</flux:description>
    @endif
    <flux:error name="data.{{ $field }}"/>
</flux:field>
