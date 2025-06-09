{{--
name: 'livewire_base_search',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div class="flex items-center border border-b-gray-200 dark:border-gray-600 p-1 rounded-md relative w-full max-w-full">
    <flux:icon.magnifying-glass class="me-1 text-gray-300 dark:text-gray-600 size-5" />

    @foreach($activeFilters as $field => $values)
        <flux:badge size="sm" class="mr-1" wire:key="filter-{{ $field }}">
            <strong class="mr-1">{{ $this->searchFields[$field] ?? ucfirst($field) }}:</strong>
            {{ implode(__t(' or ', 'Web'), $values) }}
            <flux:badge.close class="cursor-pointer" wire:click="removeFilterGroup('{{ $field }}')"/>
        </flux:badge>
    @endforeach

    <input
        wire:model.live.debounce.50ms="search"
        wire:keydown.arrow-down.prevent="incrementSelection"
        wire:keydown.arrow-up.prevent="decrementSelection"
        wire:keydown.enter.prevent="selectHighlightedItem"
        wire:keydown.escape.window="resetSearch"
        wire:keydown.backspace="handleBackspace"
        placeholder="{{ __t('Search...', 'Web') }}"
        class="border-0 focus:ring-0 focus:border-0 focus:outline-none hover:border-0 ml-1.2 w-full md:min-w-[25rem] text-sm"
    />

    @if ($showDropdown && count($this->searchFields) > 0)
        <div
            class="absolute w-full min-h-10 border mt-27.5 -ml-1 p-2 rounded-md bg-white  text-sm z-10 shadow-lg shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-800 dark:border-gray-600"
            wire:mouseleave="resetSelection"
        >

            @foreach($this->searchFields as $index => $field)
                <div
                    wire:click="addFilter('{{ $index }}')"
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
    <flux:dropdown position="bottom" align="end" offset="-5">
        <flux:button size="xs" class="cursor-pointer">
            <flux:icon.chevron-down class="mt-0.5 size-2.5"/>
        </flux:button>
        <flux:menu class="w-full md:w-[36rem] lg:w-[48rem] mt-1 max-w-full">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4">
                <div class="space-y-4 border-r dark:border-r-gray-600 pr-4">
                    @foreach($availableFilters as $groupName => $groupData)
                        @if(($groupData['column'] ?? 1) === 1)
                            <flux:menu.group heading="{{ $groupData['label'] }}">
                                @foreach($groupData['filters'] as $key => $filter)
                                    <div class="space-y-1 pl-2">
                                        @switch($filter['type'])
                                            @case('select')
                                                <select wire:model.live="activeFilters.{{ $key }}" class="block w-full rounded-md ...">
                                                    <option value="">{{ $filter['label'] }}</option>
                                                    @foreach($filter['options'] as $optionValue => $optionLabel)
                                                        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                                    @endforeach
                                                </select>
                                                @break
                                            @case('boolean')
                                                <flux:field variant="inline" class="mt-1">
                                                    <flux:checkbox wire:model.live="activeFilters.{{ $key }}" />
                                                    <flux:label>{{ $filter['label'] }}</flux:label>
                                                </flux:field>
{{--                                                <label class="inline-flex items-center">--}}
{{--                                                    <input wire:model.live="activeFilters.{{ $key }}" type="checkbox" class="rounded ..." value="1">--}}
{{--                                                    <span class="ml-2 text-sm">{{ $filter['label'] }}</span>--}}
{{--                                                </label>--}}
                                                @break
                                            @case('date')
                                                <label class="block text-sm font-medium">{{ $filter['label'] }}</label>
                                                <input wire:model.live.debounce.500ms="activeFilters.{{ $key }}" type="date" class="block w-full rounded-md ...">
                                                @break
                                        @endswitch
                                    </div>
                                @endforeach
                            </flux:menu.group>
                        @endif
                    @endforeach
                </div>

                <div class="space-y-4 border-r dark:border-r-gray-600 pr-4">
                    @foreach($availableFilters as $groupName => $groupData)
                        @if(($groupData['column'] ?? 1) === 2)
                            <flux:menu.group heading="{{ $groupData['label'] }}">
                                @foreach($groupData['filters'] as $key => $filter)
                                    <div class="space-y-1 pl-2">
                                        {{-- KORREKTUR: Der Switch-Block ist jetzt hier vollständig eingefügt --}}
                                        @switch($filter['type'])
                                            @case('select')
                                                <flux:select variant="listbox" searchable placeholder="{{ $filter['label'] }}">
                                                    @foreach($filter['options'] as $optionValue => $optionLabel)
                                                        <flux:select.option value="{{ $optionValue }}">{{ $optionValue }}</flux:select.option>
                                                    @endforeach
                                                </flux:select>
{{--                                                <select wire:model.live="activeFilters.{{ $key }}" class="block w-full rounded-md ...">--}}
{{--                                                    <option value="">{{ $filter['label'] }}</option>--}}
{{--                                                    @foreach($filter['options'] as $optionValue => $optionLabel)--}}
{{--                                                        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>--}}
{{--                                                    @endforeach--}}
{{--                                                </select>--}}
                                                @break
                                            @case('boolean')
                                                <label class="inline-flex items-center">
                                                    <input wire:model.live="activeFilters.{{ $key }}" type="checkbox" class="rounded ..." value="1">
                                                    <span class="ml-2 text-sm">{{ $filter['label'] }}</span>
                                                </label>
                                                @break
                                            @case('date')
                                                <label class="block text-sm font-medium">{{ $filter['label'] }}</label>
                                                <input wire:model.live.debounce.500ms="activeFilters.{{ $key }}" type="date" class="block w-full rounded-md ...">
                                                @break
                                        @endswitch
                                    </div>
                                @endforeach
                            </flux:menu.group>
                        @endif
                    @endforeach
                </div>

                <div class="space-y-2">
                    <flux:menu.group heading="Support">
                        <flux:menu.item>FAQ</flux:menu.item>
                        <flux:menu.item>Kontakt</flux:menu.item>
                        <flux:menu.item>Helpdesk</flux:menu.item>
                    </flux:menu.group>
                </div>
            </div>
        </flux:menu>
    </flux:dropdown>
</div>
