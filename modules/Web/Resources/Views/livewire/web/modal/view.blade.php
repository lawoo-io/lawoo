{{--
name: 'livewire_base_modal_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<flux:modal wire:model.self="show" variant="default">
    <div class="space-y-6">
        @isset($content)
        {!! $content !!}
        @endisset
    </div>
</flux:modal>
