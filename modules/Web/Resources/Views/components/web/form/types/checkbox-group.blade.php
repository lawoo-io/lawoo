{{--
name: 'form_type_checkbox_group',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'options'
])
@if (isset($options['mode']) && $options['mode'] === 'cards')
    <div class="{{ $options['class'] }}">
        <flux:label wire:dirty.class="!text-yellow-500" wire:target="data.{{ $field }}" class="pb-1">{{ $options['label'] }}</flux:label>
        <flux:checkbox.group :variant="$options['mode']">
            @foreach($options['options'] as $option)
                <flux:checkbox
                    wire:model="data.{{ $field }}"
                    :value="$option['id']"
                    :label="$option['name']"
                    :description="$option['description']"
                />
            @endforeach
        </flux:checkbox.group>
    </div>
@else
    <div class="{{ $options['class'] }}">
        <flux:checkbox.group >
            <flux:label wire:dirty.class="!text-yellow-500" wire:target="data.{{ $field }}" class="pb-1">{{ $options['label'] }}</flux:label>
            @foreach($options['options'] as $option)
                <flux:checkbox :value="$option['id']" wire:model="data.{{ $field }}" :label="$option['name']"/>
            @endforeach
        </flux:checkbox.group>
    </div>
@endif
