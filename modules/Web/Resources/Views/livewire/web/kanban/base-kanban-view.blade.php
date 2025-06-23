{{--
name: 'livewire_base_kanban_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    <x-web.kanban.view>
        <x-slot:toolbar>
            <flux:heading level="1" size="l">
                <livewire:web.breadcrumbs.breadcrumbs :pageTitle="$this->title" />
            </flux:heading>
        </x-slot:toolbar>

        <x-slot:bulkActions>
        </x-slot:bulkActions>

        <x-slot:toolbarCenter>
            @if($this->getSelectedCount() > 0)
                <flux:badge>
                    @if(!$this->selectedAllRecords)
                        {{ $this->getSelectedCount() }} {{ __t('selected', 'Web') }}
                    @else
                        {{ __t('Selected all', 'Web') }}: {{ $this->selectedAllRecords }}
                    @endif
                    @if($this->hasMorePages && !$this->selectedAllRecords)
                        <button wire:click="selectAllRecords" class="ml-1 inline-block">
                            <flux:badge color="cyan" class="cursor-pointer py-0 ml-2">{{ __t('Select all', 'Web') }}</flux:badge>
                        </button>
                    @endif
                    @isset($header)
                        {!! $header !!}
                    @endisset
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
                    <flux:badge.close wire:click="clearSelection" class="cursor-pointer"/>
                </flux:badge>
            @elseif($this->showSearch)
                <livewire:web.search.base-search
                    :searchFields="$this->searchFields"
                    :availableFilters="$this->availableFilters"
                    :searchFilters="$this->searchFilters"
                    :panelFilters="$this->panelFilters"
                />
            @endif

        </x-slot:toolbarCenter>

        <x-slot:actions>
            @isset($viewButtons)
                <div class="mr-3">
                    {!! $viewButtons !!}
                </div>
            @endisset
            <flux:input wire:model.live.debounce.2s="perPage" class="!w-12" size="xs"/>
            @if($data->hasPages())
                <flux:pagination :paginator="$data" class="!border-none !py-0 ml-2" />
            @endif
        </x-slot:actions>

        @if ($this->type === 'default')
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($data as $item)
                    <div class="relative p-3 border dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700" wire:key="{{ $item['id'] }}">
                        {{-- Navigierbarer Hauptbereich --}}
                        @if($this->formViewRoute)
                            <a href="{{ route($this->formViewRoute, [$item['id']]) }}"
                               wire:navigate
                               class="absolute inset-0 z-5 cursor-pointer">
                            </a>
                        @endif

                        @if($this->availableColumns)
                            <div class="pointer-events-auto z-10 absolute top-1 right-1.5">
                                <flux:dropdown position="bottom" align="end" class="ml-2">
                                    <flux:button variant="ghost" size="xs" icon="ellipsis-horizontal" inset="top right bottom" />
                                    <flux:menu>
                                        @foreach($this->availableOptions as $function => $options)
                                            <flux:menu.item
                                                class="size-6 cursor-pointer"
                                                variant="{{ isset($options['variant']) ? $options['variant'] : 'default' }}"
                                                wire:click="{{ isset($options['click']) ? $function . '(' . $item['id'] . ')' : false }}"
                                            >
                                                {{ $options['label'] ?? '' }}
                                            </flux:menu.item>
                                        @endforeach
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        @endif

                        <div class="relative z-0">
                            <div class="grid-cols-6">
                                @foreach($this->availableColumns as $key => $field)
                                    @if(isset($field['visible']) && $field['visible'])
                                        <x-web.kanban.types :field="$field" :value="$item[$key]"/>
                                    @endif
                                @endforeach

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else

        @endif

        <x-slot:footer>
            <flux:input wire:model.live.debounce.1s="perPage" class="!w-12" size="xs"/>
            @if($data->hasPages())
                <flux:pagination :paginator="$data" class="!border-none !py-0 ml-2" />
            @endif
        </x-slot:footer>

    </x-web.kanban.view>
</div>
