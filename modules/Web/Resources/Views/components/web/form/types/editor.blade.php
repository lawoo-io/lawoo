{{--
name: 'form_type_editor',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'options'
])
<div class="{{ $options['class'] ?? '' }}">
    @if($options['mode'] && $options['mode'] === 'code')
        <div class="code-editor" data-languages="{{ $options['languages'] ?? '' }}" @if($options['ignore'] ?? true) wire:ignore @endif></div>
        <input type="hidden" wire:model="data.{{ $field }}"/>
    @endif
    <flux:error name="data.{{ $field }}"/>
</div>
