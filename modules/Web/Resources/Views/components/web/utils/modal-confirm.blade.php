{{--
name: 'modal_confirm',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}

<flux:modal name="modal-confirm" class="min-w-[30rem]">
    <div class="space-y-6">
        <div>
            @isset($confirmData['heading'])
                <flux:heading size="lg">{{ $confirmData['heading'] }}</flux:heading>
            @endisset

            @isset($confirmData['text'])
                <flux:text class="my-3">
                    {!! $confirmData['text'] !!}
                </flux:text>
            @endisset
        </div>
        <div class="flex gap-2">
            <flux:spacer/>
            <flux:modal.close>
                <flux:button size="sm" variant="ghost" class="cursor-pointer">{{ __t('Cancel', 'Web') }}</flux:button>
            </flux:modal.close>
            @isset($confirmData['button'])
                <flux:button
                    size="sm"
                    :variant="$confirmData['button']['variant'] ?? 'outline'"
                    :wire:click="$confirmData['button']['click'] ?? false"
                >
                    {{ $confirmData['button']['label'] ?? __t('Ok', 'Web') }}
                </flux:button>
            @endisset
        </div>
    </div>
</flux:modal>
