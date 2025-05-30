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
    @include('web.partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 test">
<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
    <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
        <x-web.svg.logo-sidebar class="size-10" />
    </a>

    <flux:navlist variant="outline">
        <flux:navlist.group :heading="__t('Platform', 'Web')" class="grid">
            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __t('Dashboard', 'Web') }}
            </flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>

    <flux:spacer/>

    <flux:navlist variant="outline">
        <flux:navlist.item icon="folder-git-2" href="https://github.com/lawoo-io/lawoo" target="_blank">
            {{ __t('Repository', 'Web') }}
        </flux:navlist.item>

        <flux:navlist.item icon="book-open-text" href="https://lawoo.io/documentation" target="_blank">
            {{ __t('Documentation', 'Web') }}
        </flux:navlist.item>
    </flux:navlist>

    <!-- Desktop User Menu -->
    <flux:dropdown position="bottom" align="start">
        <flux:profile
            :name="auth()->user()->name"
{{--            :initials="auth()->user()->initials()"--}}
            icon-trailing="chevrons-up-down"
        />

        <flux:menu class="w-[220px]">

            <flux:menu.radio.group>
                <flux:menu.item :href="route('profile.form')" icon="cog" wire:navigate>{{ __t('Settings', 'Web') }}</flux:menu.item>
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
</flux:sidebar>

<flux:main>
    {{ $slot }}
</flux:main>

@fluxScripts
</body>

</html>
