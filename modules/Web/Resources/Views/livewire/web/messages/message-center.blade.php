{{--
name: 'livewire_message_center',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div
    class="w-full border rounded-lg mb-4 dark:border-gray-600">
    <flux:navbar class="w-full border-b dark:border-b-gray-600 !py-1.5">
        <flux:navbar.item
            :current="$this->message_type === 'email'"
            wire:click="setEmailType"
            class="cursor-pointer ml-2 data-current:after:hidden"
            icon="envelope"
            iconVariant="micro"
        >
           {{ __t('Message', 'Web') }}
        </flux:navbar.item>
        <flux:navbar.item
            :current="$this->message_type === 'note'"
            wire:click="setNoteType"
            class="cursor-pointer ml-2 data-current:after:hidden"
            icon="pencil"
            iconVariant="micro"
        >
            {{ __t('Note', 'Web') }}
        </flux:navbar.item>
    </flux:navbar>
    @if (!$this->showEditor)
    <input
        placeholder="{{ __t('Write something...', 'Web') }}"
        type="text"
        class="px-4 py-2 text-sm w-full focus-visible:outline-none focus-visible:ring-0 mb-1"
        wire:click="activateEditor"
    >
    @else
    <flux:editor wire:model.live.debounce.1s="body" row="auto"
                 x-ref="editor"
                 class="!border-0 !rounded-none !shadow-none **:data-[slot=content]:min-h-[30px]! relative"
                 placeholder="{{ __t('Write something...', 'Web') }}"
    >
        <flux:editor.toolbar class="rounded-t-none ">
            <flux:editor.heading/>
            <flux:editor.separator/>
            <flux:editor.bold />
            <flux:editor.italic />
            <flux:editor.strike />
            <flux:editor.separator />
            <flux:editor.bullet />
            <flux:editor.ordered />
            <flux:editor.separator />
            <flux:editor.link />
            <flux:editor.separator />
            <flux:editor.align />

            <flux:editor.spacer />

            <flux:dropdown position="bottom end" offset="-5">
                <flux:editor.button icon="ellipsis-horizontal" tooltip="{{ __t('More', 'Web') }}"/>
                <flux:menu >
                    <flux:menu.item wire:click="toggleSubject" class="size-6 cursor-pointer">
                        {{ __t('Title toggle', 'Web') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </flux:editor.toolbar>
        @if ($this->showSubject)
        <div class="flex items-center px-4 pt-4">
            <input
                type="text"
                class="flex-1 border outline-0 border-none text-sm"
                placeholder="{{ $this->subjectPlaceholders[$this->message_type] ?? $this->subjectPlaceholders['default'] }}"
            />
            <flux:icon.x-mark wire:click="toggleSubject" variant="micro" class="text-gray-400 hover:text-gray-700 cursor-pointer"/>
        </div>
        @endif
        <flux:editor.content class="dark:bg-gray-800" />
    </flux:editor>
    <div class="p-4">
        <flux:error name="body" class="text-xs mt-0 mb-1"/>
        <flux:button size="xs" variant="primary" class="cursor-pointer" wire:click="save">{{ $this->message_type === 'email' ? __t('Send', 'Web') : __t('Save', 'Web') }}</flux:button>
        <flux:button size="xs" variant="ghost" wire:click="deactivateEditor" class="cursor-pointer">{{ __t('Cancel', 'Web') }}</flux:button>
    </div>
    @endif
</div>
