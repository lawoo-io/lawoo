{{--
name: 'form_type_cards',
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
    @if(isset($options['label']))
        <flux:label wire:dirty.class="!text-yellow-500" wire:target="data.{{ $field }}">{{ $options['label'] }}</flux:label>
    @endif
    <x-dynamic-component :component="$options['component']" :items="$options['items']" :modal="$options['modal'] ?? false">
        {{ __t('Warning: Something went wrong.', 'Web') }}
    </x-dynamic-component>
</div>
