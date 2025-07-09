{{--
name: 'modal_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}

<flux:modal name="modal-view" :variant="$variant ?? 'flyout'" :position="$position ?? 'right'">
    <div class="space-y-6">
        @isset($modalContent)
            {!! $modalContent !!}
        @endisset
    </div>
</flux:modal>
