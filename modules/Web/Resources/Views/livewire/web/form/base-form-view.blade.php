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
        <div class="lg:col-span-{{ $showRightContent ? '4' : '6' }} border dark:border-gray-600 rounded-lg p-4">
            <form wire:submit.prevent="save" class="grid grid-cols-1 lg:grid-cols-12 gap-4 ">
                @csrf
                @if(isset($formTopLeft) || isset($formTopRight))
                <div class="flex md:justify-between items-center pb-4 border-b dark:border-b-gray-600 lg:col-span-12">
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
                        <flux:tab.group class="mt-2 lg:col-span-12">
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
                <div class="lg:col-span-12">
                    <flux:button type="submit" size="sm" variant="primary" disabled wire:dirty.attr.remove="disabled" class="cursor-pointer">{{ __t('Save', 'Web') }}</flux:button>
                </div>
            </form>

        </div>

        <!-- Zweite Spalte: Optional -->
        @if ($showRightContent)
        <div class="lg:col-span-2">
            <div class="w-full border rounded-lg mb-4 dark:border-gray-600">
                <flux:navbar class="w-full border-b dark:border-b-gray-600 !py-1.5">
                    <flux:navbar.item :current="true" href="#" class="ml-2 data-current:after:hidden">Message</flux:navbar.item>
                    <flux:navbar.item href="#">Note</flux:navbar.item>
                </flux:navbar>
                <input
                    placeholder="Type a message..."
                    type="text"
                    class="px-4 py-2 text-sm w-full focus-visible:outline-none focus-visible:ring-0 mb-1"
                >
{{--                <flux:editor row="auto"--}}
{{--                             class="!border-0 !rounded-none !shadow-none **:data-[slot=content]:min-h-[50px]!">--}}
{{--                    <flux:editor.toolbar class="h-9">--}}
{{--                        <flux:editor.heading/>--}}
{{--                        <flux:editor.separator/>--}}
{{--                        <flux:editor.bold />--}}
{{--                        <flux:editor.italic />--}}
{{--                        <flux:editor.strike />--}}
{{--                        <flux:editor.separator />--}}
{{--                        <flux:editor.bullet />--}}
{{--                        <flux:editor.ordered />--}}
{{--                        <flux:editor.separator />--}}
{{--                        <flux:editor.link />--}}
{{--                        <flux:editor.separator />--}}
{{--                        <flux:editor.align />--}}
{{--                    </flux:editor.toolbar>--}}
{{--                    <flux:editor.content />--}}
{{--                </flux:editor>--}}
{{--                <div class="p-4">--}}
{{--                    <flux:button size="xs" variant="primary">Send</flux:button>--}}
{{--                    <flux:button size="xs" variant="ghost">Cancel</flux:button>--}}
{{--                </div>--}}
            </div>

            <!-- History -->
            <div class="p-4 rounded-lg border dark:border-gray-600">
                <div class="flex flex-row sm:items-center gap-2">
                    <div>
                        <flux:avatar src="https://randomuser.me/api/portraits/men/1.jpg" size="xs" class="shrink-0" />
                    </div>
                    <div class="flex flex-col gap-0.5 sm:gap-2 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-2">
                            <flux:heading>John Doe</flux:heading>
                            <flux:icon.envelope class="size-4.5 text-gray-500 dark:text-gray-200"/>
{{--                            <flux:badge color="lime" size="sm" icon="check-badge" inset="top bottom">Moderator</flux:badge>--}}
                        </div>
                        <flux:text class="text-sm">2 days ago</flux:text>
                    </div>
                </div>
                <div class="min-h-2 sm:min-h-1"></div>
                <div class="pl-8">
                    <flux:text variant="strong">
                        <p>
                            I hope you’re doing well.
                        </p>
                        <p>
                            I’m reaching out to ask for more information regarding [brief topic, e.g., the upcoming project meeting or product details].
                            Could you please provide me with the relevant details or documents?
                        </p>
                        <p>
                            Looking forward to your response.
                        </p>
                    </flux:text>
                    <div class="min-h-2"></div>
                </div>
            </div>
        </div>
        @endif
    </x-web.form.view>
</div>
