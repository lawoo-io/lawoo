{{--
name: 'livewire_nav_top',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<flux:navbar scrollable class="pl-2.5">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    @if($subNavList)
        @foreach ($subNavList as $level1Nav)
            @if(!$level1Nav->hasChildren())
                <flux:navbar.item
                    :icon="$level1Nav->icon ?: null"
                    href="{{ route($level1Nav->route) }}"
                    :current="request()->routeIs($level1Nav->route) || request()->routeIs($level1Nav->route . '.*')"
                    wire:navigate
                >
                    {{ $level1Nav->name }}
                </flux:navbar.item>
            @else
                <flux:dropdown>
                    <flux:navbar.item icon:trailing="chevron-down">{{ $level1Nav->name }}</flux:navbar.item>
                    <flux:navmenu>
                        @foreach($level1Nav->children as $level2Nav)
                            <flux:navbar.item
                                :icon="$level2Nav->icon ?: null"
                                :href="route($level2Nav->route)"
                                :current="request()->routeIs($level2Nav->route)"
                                wire:navigate
                            >
                                {{ $level2Nav->name }}
                            </flux:navbar.item>
                        @endforeach
                    </flux:navmenu>
                </flux:dropdown>
            @endif
        @endforeach
    @endif
</flux:navbar>
