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
                <livewire:web.breadcrumbs.breadcrumbs :pageTitle="$this->data['name']" />
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
                    <flux:menu.item wire:click="delete" wire:confirm="{{ __t('Are you sure you want to delete the selected items?', 'Web') }}" class="size-8 cursor-pointer">
                        <flux:icon.trash class="size-4 mr-2 text-red-600" />
                        {{ __t('Remove', 'Web') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </x-slot:actions>

        <!-- Erste Spalte: nimmt 12 oder 8 Spalten ein, je nach Sichtbarkeit der zweiten -->
        <div class="md:col-span-{{ $showRightContent ? '8' : '12' }} border dark:border-gray-600 rounded-lg p-4">
            <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-12 gap-4 ">
                @csrf
                @if(isset($formTopLeft) || isset($formTopRight))
                <div class="flex md:justify-between items-center pb-4 border-b dark:border-b-gray-600 md:col-span-12">
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
                @foreach($this->fields as $field => $options)
                    @if ($field !== 'tabs' && $field !== 'tab_*')
                        <x-web.form.types :field="$field" :options="$options"/>
                    @elseif($field === 'tabs')
                        <flux:tab.group class="mt-2 md:col-span-12">
                            <flux:tabs>
                                @foreach($this->fields['tabs'] as $tabKey => $tabOptions)
                                    <flux:tab :name="$tabKey" class="cursor-pointer" :icon="$tabOptions['icon'] ?? false">{{ $tabOptions['label'] }}</flux:tab>
                                @endforeach
                            </flux:tabs>
                            @foreach($this->fields['tabs'] as $tabKey => $tabOptions)
                                <flux:tab.panel :name="$tabKey" class="md:col-span-12">
                                    @foreach($tabOptions['fields'] as $field => $options)
                                        <x-web.form.types :field="$field" :options="$options"/>
                                    @endforeach
                                </flux:tab.panel>
                            @endforeach
                        </flux:tab.group>
                    @endif
                @endforeach
                <div class="md:col-span-6">
                    <flux:button type="submit" variant="primary" disabled wire:dirty.attr.remove="disabled" class="cursor-pointer">{{ __t('Save', 'Web') }}</flux:button>
                </div>
            </form>

        </div>

        <!-- Zweite Spalte: Optional -->
        @if ($showRightContent)
        <div class="hidden md:block md:col-span-4 border p-4 rounded-md">
            <h2 class="text-xl font-bold mb-2">Fenster 2</h2>
            <p>Wird nur angezeigt, wenn gewünscht.</p>
        </div>
        @endif
    </x-web.form.view>
</div>
