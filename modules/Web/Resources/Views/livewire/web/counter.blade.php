{{--
name: 'livewire-counter',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    <p>Count: {{ $count }}</p>
    <flux:button variant="primary" wire:click="increment">Click</flux:button>
</div>
