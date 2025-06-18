{{--
name: 'livewire_base_search',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div class="flex items-center border border-b-gray-200 dark:border-gray-600 p-1 rounded-md relative w-full lg:min-w-[35rem]">
    <flux:icon.magnifying-glass class="me-1 text-gray-300 dark:text-gray-600 size-5" />

    <div class="flex flex-wrap  lg:max-w-[45vw] md:max-w-[35vw] w-full gap-1">

        @foreach($panelFilters as $field => $value)
            @if(isset($flatAvailableFilters[$field]))
                @php
                    $filterConfig = $flatAvailableFilters[$field];
                    $operator = $filterConfig['operator'] ?? '=';
                    $isBoolean = ($filterConfig['type'] === 'boolean');
                    $isDate = in_array($filterConfig['type'], ['date', 'datepicker']) && isset($filterConfig['formats']);
                    $isRelation = in_array($filterConfig['type'], ['relation']);
                @endphp
                <flux:badge size="sm" color="cyan" class="mr-1" icon="funnel" wire:key="panel-filter-{{ $field }}">
                    <strong class="mr-1">
                        {{ $filterConfig['label'] }}
                    </strong>
                        @if($operator === 'whereIn')
                            <i class="mr-1">{{ __t('in', 'Web') }}</i>
                        @elseif($operator === 'notWhereIn')
                            <i class="mr-1">{{ __t('not in', 'Web') }}</i>
                        @elseif($operator !== '=' && $operator !== 'date_between' && $operator !== 'between')
                            <i class="mr-1">{{ $operator }}</i>
                        @else
                            :
                        @endif
                    @if($isBoolean)
                        {{ $value ? __t('yes','Web') : __t('no','Web') }}
                    @elseif($isDate)
                        @php
                            $locale = app()->getLocale();
                            $format = $filterConfig['formats'][$locale] ?? 'Y-m-d'; // Fallback auf Standardformat
                        @endphp
                        @if(is_array($value) && count($value) >= 2)
                            {{ \Carbon\Carbon::parse($value['start'])->format($format) }}
                            <span class="mx-1">{{ __t('to', 'Web') }}</span>
                            {{ \Carbon\Carbon::parse($value['end'])->format($format) }}
                        @elseif(!is_array($value) && !empty($value))
                            {{ \Carbon\Carbon::parse($value)->format($format) }}
                        @endif
                    @elseif($isRelation)
                        @if(is_array($value))
                            @foreach($value as $name => $id)
                                {{ $name }}@unless($loop->last), @endunless
                            @endforeach
                        @endif
                    @else
                        {{ $filterConfig['options'][$value] ?? $value }}
                    @endif
                    <flux:badge.close class="cursor-pointer" wire:click="removePanelFilter('{{ $field }}')"/>
                </flux:badge>
            @endif
        @endforeach

        @foreach($searchFilters as $field => $values)
            <flux:badge size="sm" class="mr-1" icon="chat-bubble-bottom-center-text" wire:key="filter-{{ $field }}">
                <strong class="mr-1">{{ $this->searchFields[$field] ?? ucfirst($field) }}:</strong>
                <div class="whitespace-nowrap overflow-hidden text-ellipsis max-w-[13rem] md:max-w-[20rem]">{{ implode(__t(' or ', 'Web'), $values) }}</div>
                <flux:badge.close class="cursor-pointer" wire:click="removeSearchFilterGroup('{{ $field }}')"/>
            </flux:badge>
        @endforeach

        <input
            wire:model.live.debounce.100ms="search"
            wire:keydown.arrow-down.prevent="incrementSelection"
            wire:keydown.arrow-up.prevent="decrementSelection"
            wire:keydown.enter.prevent="selectHighlightedItem"
            wire:keydown.escape.window="resetSearch"
            wire:keydown.backspace="handleBackspace"
            placeholder="{{ __t('Search...', 'Web') }}"
            class="border-0 focus:ring-0 focus:border-0 focus:outline-none hover:border-0 ml-1.2 text-sm grow"
        />
    </div>

    @if ($showDropdown && count($this->searchFields) > 0)
        <div
            class="absolute w-full min-h-10 border mt-27.5 -ml-1 p-2 rounded-md bg-white  text-sm z-10 shadow-lg shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-800 dark:border-gray-600"
            wire:mouseleave="resetSelection"
        >
            @foreach($this->searchFields as $index => $field)
                <div
                    wire:click="addSearchFilter('{{ $index }}')"
                    wire:mouseenter="setSelection({{ $loop->index }})"
                    @class([
                        'cursor-pointer w-full py-1 px-2 rounded-md',
                        'bg-gray-100 dark:bg-gray-600' => $selectedIndex === $loop->index
                    ])
                >
                    <span>{{ $field['label'] ?? $field }}</span>: {{ $this->search }}
                </div>
            @endforeach
        </div>
    @endif
    <div x-data="{ open: false }" class="ml-auto">
        <flux:button @click="open = !open" size="xs" class="cursor-pointer">
            <flux:icon.chevron-down class="mt-0.5 size-2.5"/>
        </flux:button>
        <div
            x-show="open"
            @click.outside="if (!$event.target.closest('dialog')) open = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute right-0 top-full w-full lg:min-w-[50rem] mt-1 py-4 z-20 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700 focus:outline-none"
            style="display: none;"
        >
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-2">
                <div class="space-y-4 md:border-r dark:border-r-gray-600 px-4">
                    @foreach($availableFilters as $groupName => $groupData)
                        @if(($groupData['column'] ?? 1) === 1)
                            <flux:menu.search.group heading="{{ $groupData['label'] }}" class="!border-b-0 !pl-0 mb-1">
                                @foreach($groupData['filters'] as $key => $filter)
                                    <div>
                                        <x-web.search.types
                                            :key="$key"
                                            :filter="$filter"
                                            :panelFilters="$panelFilters"
                                        />
                                    </div>
                                @endforeach
                            </flux:menu.search.group>
                        @endif
                    @endforeach
                </div>

                <div class="space-y-4 md:border-r dark:border-r-gray-600 pr-4">
                    @foreach($availableFilters as $groupName => $groupData)
                        @if(($groupData['column'] ?? 1) === 2)
                            <flux:menu.search.group heading="{{ $groupData['label'] }}" class="mb-1">
                                @foreach($groupData['filters'] as $key => $filter)
                                    <x-web.search.types x-if="open"
                                        :key="$key"
                                        :filter="$filter"
                                        :panelFilters="$panelFilters"
                                    />
                                @endforeach
                            </flux:menu.search.group.group>
                        @endif
                    @endforeach
                </div>

                <div class="space-y-2">
                    <livewire:web.search.custom-search :panelFilters="$this->panelFilters" :searchFilters="$this->searchFilters" />
                </div>
            </div>
        </div>
    </div>
</div>
