{{--
name: 'livewire_base_list_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    <x-web.list.view>
        <x-slot:toolbar>
            <flux:heading level="1" size="xl">
                {{ $this->title }}
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
                            {{ $header }}
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

            @endif

        </x-slot:toolbarCenter>

        <x-slot:actions>
            <flux:input wire:model.live.debounce.1s="perPage" class="!w-12" size="xs"/>
            @if($data->hasPages())
                <flux:pagination :paginator="$data" class="!border-none !py-0 ml-2" />
            @endif
        </x-slot:actions>

        <x-slot:columnts>
            @if($this->checkboxes)
                <flux:table.column class="w-2">
                    <flux:checkbox
                        wire:model.live="selectAll"
                        :disabled="!$data"
                        :checked="$this->selectAll = $this->getSelectedCount() == count($data)"
                        wire:click="toggleSelectAll"
                    />
                </flux:table.column>
            @endif
            @foreach($this->availableColumns as $column => $options)
                @if(in_array($column, $this->visibleColumns))
                    <flux:table.column
                        :sortable="in_array($column, $this->sortColumns)"
                        :wire:click="in_array($column, $this->sortColumns) ? 'sort(\'' . $column . '\')' : null"
                        :sorted="in_array($column, $this->sortColumns) && $sortBy === $column"
                        :direction="in_array($column, $this->sortColumns) ? $sortDirection : null"
                        :class="$column === 'id' ? 'w-2' : ''"
                    >
                        {{ $options['label'] }}
                    </flux:table.column>
                @endif
            @endforeach
            <flux:table.column align="end">
                <flux:dropdown position="bottom" align="end">
                    <flux:button size="sm" icon="list-bullet"></flux:button>
                    <flux:menu>
                        @foreach($this->availableColumns as $key => $options)
                            <flux:menu.checkbox
                                wire:click="toggleColumn('{{ $key }}')"
                                :checked="$this->isVisible($key)"
                            >
                                {{ $options['label'] }}
                            </flux:menu.checkbox>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            </flux:table.column>
        </x-slot:columnts>

        <x-slot:body>
            @foreach($data as $item)
                <flux:table.row wire:key="{{ $item->{$this->keyField} }}">
                    @if($this->checkboxes)
                        <flux:table.cell>
                            <flux:checkbox
                                wire:model.live="selected"
                                value="{{ $item->id }}"
                                :key="'list-' . $item->id"
                            />
                        </flux:table.cell>
                    @endif
                    @foreach($this->availableColumns as $column => $options)
                        @if(in_array($column, $this->visibleColumns))
                        <flux:table.cell>
                            @if($options['type'] ?? false)
                                <x-web.list.types :type="$options['type']" :value="$item->{$column}"/>
                            @else
                                {{ $item->{$column} }}
                            @endif
                        </flux:table.cell>
                        @endif
                    @endforeach
                </flux:table.row>
            @endforeach
        </x-slot:body>

        <x-slot:footer>
            <flux:input wire:model.live.debounce.1s="perPage" class="!w-12" size="xs"/>
            @if($data->hasPages())
            <flux:pagination :paginator="$data" class="!border-none !py-0 ml-2" />
            @endif
        </x-slot:footer>
    </x-web.list.view>
</div>
