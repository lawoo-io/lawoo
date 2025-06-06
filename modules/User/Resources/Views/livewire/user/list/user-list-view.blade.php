{{--
name: 'livewire_user_list_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    <x-web.list.view>
        <x-slot:toolbar>
            <p>Toolbar</p>
            <p>Selected: {{ $this->getSelectedCount() }}</p>

        </x-slot:toolbar>

        <x-slot:bulkActions>
            <p>BulkActions</p>
        </x-slot:bulkActions>

        <x-slot:actions>
            <p>Actions test</p>
        </x-slot:actions>

        <x-slot:columnts>
            <flux:table.column class="w-2">
                <flux:checkbox
                    wire:model.live="selectAll"
                    :disabled="!$data"
                    :checked="$this->selectAll = $this->getSelectedCount() == count($data)"
                    wire:click="toggleSelectAll"
                />
            </flux:table.column>
            @if($this->isVisible('name'))
            <flux:table.column sortable wire:click="sort('name')" :sorted="$sortBy === 'name'" :direction="$sortDirection">{{ __t('Name', 'User') }}</flux:table.column>
            @endif
            @if($this->isVisible('email'))
            <flux:table.column>{{ __t('Email', 'User') }}</flux:table.column>
            @endif
            @if($this->isVisible('is_active'))
            <flux:table.column sortable wire:click="sort('is_active')" :sorted="$sortBy === 'is_active'" :direction="$sortDirection">{{ __t('Active', 'User') }}</flux:table.column>
            @endif
            @if($this->isVisible('created_at'))
            <flux:table.column>{{ __t('Created at', 'User') }}</flux:table.column>
            @endif
            @if($this->isVisible('updated_at'))
                <flux:table.column>{{ __t('Updated at', 'User') }}</flux:table.column>
            @endif
            <flux:table.column align="end">
                <flux:dropdown position="bottom" align="end">
                    <flux:button size="sm" icon="list-bullet"></flux:button>
                    <flux:menu>
                        @foreach($this->getAvailableColumns() as $key => $label)
                            <flux:menu.checkbox
                                wire:click="toggleColumn('{{ $key }}')"
                                :checked="$this->isVisible($key)"
                            >
                                {{ $label['label'] }}
                            </flux:menu.checkbox>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            </flux:table.column>
        </x-slot:columnts>

        <x-slot:body>
            @foreach($data as $user)
                <flux:table.row wire:key="{{ $user->id }}" data-url="#">
                    <flux:table.cell>
                        <flux:checkbox
                            wire:model.live="selected"
                            value="{{ $user->id }}"
                            :key="'user-' . $user->id"
                        />
                    </flux:table.cell>
                    @if($this->isVisible('name'))
                    <flux:table.cell>
                        {{ $user->name }}
                    </flux:table.cell>
                    @endif
                    @if($this->isVisible('email'))
                    <flux:table.cell>
                        {{ $user->email }}
                    </flux:table.cell>
                    @endif
                    @if($this->isVisible('is_active'))
                    <flux:table.column>
                        @if($user->is_active)
                            <flux:badge size="sm" color="lime">{{ __t('Yes', 'User') }}</flux:badge>
                        @else
                            <flux:badge size="sm" color="red">{{ __t('No', 'User') }}</flux:badge>
                        @endif
                    </flux:table.column>
                    @endif
                    @if($this->isVisible('created_at'))
                        <flux:table.cell>
                            {{ $user->created_at }}
                        </flux:table.cell>
                    @endif
                    @if($this->isVisible('updated_at'))
                        <flux:table.cell>
                            {{ $user->updated_at }}
                        </flux:table.cell>
                    @endif
                </flux:table.row>
            @endforeach
        </x-slot:body>

        <x-slot:footer>
            <flux:table :paginate="$data">

            </flux:table>
        </x-slot:footer>
    </x-web.list.view>
</div>
