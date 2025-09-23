{{--
name: 'form_type_pillbox',
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
    <flux:label wire:dirty.class="!text-yellow-500" wire:target="data.{{ $field }}" class="pb-1">{{ $options['label'] }}</flux:label>
    <flux:pillbox wire:model="data.{{ $field }}" multiple placeholder="{{ __t('Choose language(s)...', 'Website') }}">
        @foreach($options['options'] as $option)
            <flux:pillbox.option :value="$option['id']">{{ $option['name'] }}</flux:pillbox.option>
        @endforeach
    </flux:pillbox>
</div>
