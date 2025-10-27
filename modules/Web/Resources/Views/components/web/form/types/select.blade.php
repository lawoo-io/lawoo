{{--
name: 'form_type_select',
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
        <flux:label wire:dirty.class="!text-yellow-500" wire:target="data.{{ $field }}">{{ $options['label'] }}</flux:label>
    @endif
    @if (isset($options['description_top']))
        <flux:description>{{ $options['description_top'] }}</flux:description>
    @endif

    <flux:select variant="listbox" wire:model="data.{{ $field }}" :wire:change="$options['change'] ?? false" searchable :placeholder="$options['placeholder'] ?? __t('--Please select--', 'Web')" :disabled="$options['disabled'] ?? false">
    @foreach($options['options'] as $key => $value)
        <flux:select.option :value="$key">{{ $value }}</flux:select.option>
    @endforeach
    </flux:select>

    @if (isset($options['description_bottom']))
        <flux:description>{{ $options['description_bottom'] }}</flux:description>
    @endif
    <flux:error name="data.{{ $field }}"/>
</flux:field>
