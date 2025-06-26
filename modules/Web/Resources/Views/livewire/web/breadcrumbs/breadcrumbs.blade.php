{{--
name: 'livewire_breadcrumbs',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<flux:breadcrumbs>
    <flux:breadcrumbs.item href="/lawoo" wire:navigate>
        <flux:icon.home class="size-4"/>
    </flux:breadcrumbs.item>
    @if(count($this->breadcrumbs) > 2)
        <flux:breadcrumbs.item>
            <flux:dropdown>
                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                <flux:navmenu>
                    @foreach($this->breadcrumbs as $key => $item)
                        @if (!$loop->first && !$loop->last)
                            <flux:menu.item href="{{ $item['url'] }}" class="cursor-pointer" wire:navigate>
                                {{ $item['name'] }}
                            </flux:menu.item>
                        @endif
                    @endforeach
                </flux:navmenu>
            </flux:dropdown>
        </flux:breadcrumbs.item>
    @endif
    <flux:breadcrumbs.item>{{ $this->pageTitle }}</flux:breadcrumbs.item>
</flux:breadcrumbs>
