{{--
name: 'layout-sidebar',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" xmlns:flug="http://www.w3.org/1999/html">
<head>
    @include('modules.web.partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 test">
<flux:sidebar id="sidebar" sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
    <flux:icon.chevron-left class="cursor-pointer !absolute right-1 mt-2 size-4 text-gray-400 hover:text-gray-700" size="xs"/>

    <a href="{{ route('lawoo.dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse " wire:navigate>
        <x-web.svg.logo-sidebar class="size-10" />
    </a>

    <!-- Navigation Left -->
    <livewire:web.nav.nav-left/>

    <flux:spacer/>

    <flux:navlist variant="outline">
        <flux:navlist.item icon="folder-git-2" href="https://github.com/lawoo-io/lawoo" target="_blank">
            {{ __t('Repository', 'Web') }}
        </flux:navlist.item>

        <flux:navlist.item icon="book-open-text" href="https://lawoo.io/documentation" target="_blank">
            {{ __t('Documentation', 'Web') }}
        </flux:navlist.item>
    </flux:navlist>

</flux:sidebar>
<flux:header class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 flex items-center lg:px-3">

    <livewire:web.nav.nav-top/>

    <flux:spacer />

    <flux:dropdown position="top" align="end">
        <flux:profile
            :initials="auth()->user()->initials()"
            class="shrink-0 cursor-pointer"
        />
        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <span class="p-1 bg-gray-200 rounded">{{ auth()->user()->initials() }}</span>
                            <span class="text-sm leading-tight truncate font-semibold">{{ auth()->user()->name }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <flux:menu.radio.group>
                <flux:menu.item :href="route('lawoo.profile.form')" icon="cog" wire:navigate>{{ __t('Settings', 'Web') }}</flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __t('Logout', 'Web') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

<flux:main class="!p-6">
    {{ $slot }}
</flux:main>

@fluxScripts
@persist('toast')
<flux:toast position="top right" class="mt-12" />
@endpersist
</body>

</html>
