{{--
name: 'livewire_nav_left',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}

<flux:navlist variant="outline">
    <flux:navlist.group class="grid">
        <flux:heading class="pl-2.5 text-gray-400 my-2">{{ __t('Modules', 'Web') }}</flux:heading>
        @foreach($navlist as $nav)
            @can($nav->middleware)
            <flux:navlist.item
                :icon="$nav->icon ?: null"
                href="{{ route($nav->route) }}"
                :current="$nav->isNavigationActive()"
                wire:navigate
            >
                <span>
                    {{ $nav->name }}
                </span>
            </flux:navlist.item>
            @endcan
        @endforeach
    </flux:navlist.group>
</flux:navlist>
