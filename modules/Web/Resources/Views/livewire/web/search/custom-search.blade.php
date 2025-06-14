{{--
name: 'livewire_custom_search',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    <flux:menu.search.group heading="{{ __t('Custom Search', 'Web') }}">
        <flux:navlist class="mb-2">
            @foreach($this->customItems as $item)
                <flux:button.group class="w-full mb-1" :wire:key="$item['id']">
                    <flux:button class="w-full justify-start cursor-pointer" size="sm" wire:click="setSearch('{{ $item['id'] }}')">
                        <div class="flex items-center">
                            @if ($item['default'] === true)<flux:icon.star variant="solid" class="size-4 text-yellow-500 justify-start mr-2" />@endif
                            <span class="justify-end">{{ $item['name'] }}</span>
                        </div>
                    </flux:button>
                    @if($item['public'] === true && auth()->user()->can('web.search.delete_public'))
                    <flux:button size="sm" class="cursor-pointer" wire:click="removeSearch('{{ $item['id'] }}', '{{ $item['public'] }}')" wire:confirm="{{ __t('Really delete?', 'Web') }}">
                        <flux:icon.trash class="size-3 text-gray-500"/>
                    </flux:button>
                    @endif
                </flux:button.group>
            @endforeach
        </flux:navlist>
        @if($this->panelFilters)
        <flux:accordion>
            <flux:accordion.item>
                <flux:accordion.heading>{{ __t('Save current filters?', 'Web') }}</flux:accordion.heading>
                <flux:accordion.content>
                    <form wire:submit="save">
                        <div class="flex gap-1 mb-1">
                            <flux:input wire:model="name" size="sm" :placeholder="__t('Name...', 'Web')"/>
                            <flux:button size="sm" variant="filled" type="submit"><flux:icon.plus class="size-3"/></flux:button>
                        </div>
                        @error('name')<div class="mb-1"><span class="error text-xs text-red-600">{{ $message }}</span></div>@enderror
                        <flux:checkbox wire:model="default" class="mb-1" label="{{ __t('Set as default', 'Web') }}"/>
                        @can('web.search.save_for_all')
                        <flux:checkbox class="size-4" wire:model="public" label="{{ __t('Public for all Users?', 'Web') }}"/>
                        @endcan
                    </form>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
        @endif
    </flux:menu.search.group.group>
</div>
