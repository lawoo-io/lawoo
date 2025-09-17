{{--
name: 'livewire_modal_stack',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    @foreach ($stack as $index => $modal)
        <div x-data="{}">
            <flux:modal name="modal-stack-{{ $index }}" class=" px-15" variant="flyout" x-on:close="$dispatch('close-modal-stack')" wire:keydown.escape="$dispatch('close-modal-stack')" style="width: {{ $width }}vw;">
                <livewire:dynamic-component :is="$modal['name']"
                             :wire:key="md5($modal['name'] . $index)"
                             :id="$modal['id'] ?? false"
                             :modal="true"
                             :type="$modal['type']"
                             :wire:props="$modal['props']"
                />
            </flux:modal>
            <div x-init="$flux.modal('modal-stack-{{ $index }}').show()"></div>
        </div>
    @endforeach
</div>
