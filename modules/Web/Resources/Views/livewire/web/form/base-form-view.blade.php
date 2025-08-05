{{--
name: 'livewire_base_form_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div xmlns:flux="http://www.w3.org/1999/html">
    <x-web.form.view>
        <x-slot:toolbar>
            <flux:heading level="1" size="xl">
                <livewire:web.breadcrumbs.breadcrumbs :pageTitle="$this->pageTitle" />
            </flux:heading>
        </x-slot:toolbar>

        <x-slot:toolbarCenter>
            @isset($headerCenter)
                <flux:button.group>
                    {!! $headerCenter  !!}
                </flux:button.group>
            @endisset
        </x-slot:toolbarCenter>

        <x-slot:actions>

            <flux:dropdown position="bottom" align="end" class="ml-2">
                <flux:button size="xs" icon="cog-6-tooth" class="cursor-pointer"/>
                <flux:menu>
                    @isset($actions)
                        {!! $actions !!}
                    @endisset
                    @if ($this->permissionForDeleting)
                        @can($this->permissionForDeleting)
                            <flux:menu.item wire:click="delete" wire:confirm="{{ __t('Are you sure you want to delete the selected items?', 'Web') }}" class="size-8 cursor-pointer">
                                <flux:icon.trash class="size-4 mr-2 text-red-600" />
                                {{ __t('Remove', 'Web') }}
                            </flux:menu.item>
                        @endcan
                    @else
                        <flux:menu.item wire:click="delete" wire:confirm="{{ __t('Are you sure you want to delete the selected items?', 'Web') }}" class="size-8 cursor-pointer">
                            <flux:icon.trash class="size-4 mr-2 text-red-600" />
                            {{ __t('Remove', 'Web') }}
                        </flux:menu.item>
                    @endif
                </flux:menu>
            </flux:dropdown>
        </x-slot:actions>

        <!-- Erste Spalte: nimmt 12 oder 8 Spalten ein, je nach Sichtbarkeit der zweiten -->
        <div class="flex-1 max-w-{{ $this->showMessages && $this->id ? '2/3' : 'full' }} ">
            <form wire:submit.prevent="save" class="border dark:border-gray-600 rounded-lg p-4">
                @csrf
                @if(isset($formTopLeft) || isset($formTopRight))
                <div class="flex md:justify-between items-center pb-4 border-b dark:border-b-gray-600 lg:col-span-12 mb-3">
                    @isset($formTopLeft)
                    <div>
                        {!! $formTopLeft !!}
                    </div>
                    @endisset
                    @isset($formTopRight)
                    <div>
                        {!! $formTopRight !!}
                    </div>
                    @endisset
                </div>
                @endif
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    @foreach($this->fields as $field => $options)
                        @if ($field !== 'tabs' && $field !== 'tab_*')
                            <x-web.form.types :field="$field" :options="$options"/>
                        @elseif($field === 'tabs')
                            <flux:tab.group class="mt-2 lg:col-span-12">
                                <flux:tabs>
                                    @foreach($this->fields['tabs'] as $tabKey => $tabOptions)
                                        <flux:tab :name="$tabKey" class="cursor-pointer" :icon="$tabOptions['icon'] ?? false">{{ $tabOptions['label'] }}</flux:tab>
                                    @endforeach
                                </flux:tabs>
                                @foreach($this->fields['tabs'] as $tabKey => $tabOptions)
                                    <flux:tab.panel :name="$tabKey" class="{{ $tabOptions['class'] ? $tabOptions['class'] : 'md:col-span-12' }}">
                                        @foreach($tabOptions['fields'] as $field => $options)
                                            <x-web.form.types :field="$field" :options="$options"/>
                                        @endforeach
                                    </flux:tab.panel>
                                @endforeach
                            </flux:tab.group>
                        @endif
                    @endforeach
                </div>

                <div class="flex items-center justify-between mt-4">
                    <flux:button
                        type="submit"
                        size="sm"
                        variant="primary"
                        class="cursor-pointer"
                        disabled
                        wire:dirty.attr.remove="disabled"
                    >
                        @if ($this->type === 'edit')
                            {{ __t('Save', 'Web') }}
                        @elseif($this->type === 'create')
                            {{ __t('Create', 'Web') }}
                        @endif
                    </flux:button>
                </div>
            </form>

        </div>

        <!-- Zweite Spalte: Optional -->
        @if ($this->showMessages && $this->id)
        <div class="w-1/3 overflow-y-auto h-[85vh]">
            <!-- Message Center -->
            <livewire:web.messages.message-center :messagesModel="$this->messagesModel" />
            <!-- History -->
            <livewire:web.messages.form-messages :messagesModel="$this->messagesModel" />
        </div>
        @endif
    </x-web.form.view>
</div>
