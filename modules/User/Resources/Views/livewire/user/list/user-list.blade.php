{{--
name: 'livewire_user_list',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    @section('actions')
        <flux:menu.item class="size-8 cursor-pointer" wire:click="sendMessage">
            <flux:icon.envelope class="size-4 mr-2" />
            {{ __t('Send message', 'User') }}
        </flux:menu.item>
    @endsection

    @section('viewButtons')
        <flux:button size="xs"
            :variant="$livewireComponent->viewType === 'list' ? 'primary' : 'outline'"
            icon="queue-list"
            class="cursor-pointer"
            wire:click="setViewType('list', true)"
        />
        <flux:button
            size="xs"
            :variant="$livewireComponent->viewType === 'kanban' ? 'primary' : 'outline'"
            icon="rectangle-group"
            class="cursor-pointer"
            wire:click="setViewType('kanban', true)"
        />
    @endsection
</div>
