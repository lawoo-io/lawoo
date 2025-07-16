{{--
name: 'form_type_switch',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'options'
])
<flux:field class="{{ $options['class'] }}" :variant="isset($options['mode']) ? $options['mode'] : ''" >
    @if (isset($options['label']))
        <flux:label wire:dirty.class="!text-yellow-500" wire:target="data.{{ $field }}">{{ $options['label'] }}</flux:label>
    @endif
    @if (isset($options['description_top']))
        <flux:description>{{ $options['description_top'] }}</flux:description>
    @endif
    <flux:switch
        wire:model="data.{{ $field }}"
        :disabled="isset($options['disabled']) && $options['disabled'] ?? false"
    />
    @if (isset($options['description_bottom']))
        <flux:description>{{ $options['description_bottom'] }}</flux:description>
    @endif
    <flux:error name="data.{{ $field }}"/>
</flux:field>
