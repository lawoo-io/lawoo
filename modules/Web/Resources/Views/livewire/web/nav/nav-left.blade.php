{{--
name: 'livewire_nav_left',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}

<flux:navlist variant="outline">
    <flux:navlist.group :heading="__t('Platform', 'Web')" class="grid">
        @foreach($navlist as $nav)
            @can($nav->middleware)
            <flux:navlist.item
                :icon="$nav->icon ?: null"
                href="{{ route($nav->route) }}"
                :current="request()->routeIs($nav->route) || request()->routeIs($nav->route . '.*')"
                wire:navigate
            >
                {{ $nav->name }}
            </flux:navlist.item>
            @endcan
        @endforeach
    </flux:navlist.group>
</flux:navlist>
