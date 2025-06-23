{{--
name: 'livewire_user_kanban_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    @section('options')
    <flux:menu.item class="size-6" wire:click="delete">{{ __t('Remove', 'User') }}</flux:menu.item>
    @endsection
</div>
