{{--
name: 'livewire_nav_left',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}

<flux:sidebar.nav>
    @foreach($navlist as $nav)
        @can($nav->middleware)
            <flux:sidebar.item
                :icon="$nav->icon ?: null"
                href="{{ route($nav->route) }}"
                :current="$nav->isNavigationActive()"
                wire:navigate
            >
                {{ $nav->name }}
            </flux:sidebar.item>
        @endcan
    @endforeach
</flux:sidebar.nav>
