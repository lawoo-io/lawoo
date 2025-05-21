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
<body class="min-h-screen bg-white dark:bg-zinc-800">
<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
    <a href="{{ route('lawoo') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
        <x-web.svg.logo-sidebar class="size-10" />
    </a>

    <flux:navlist variant="outline">
        <flux:navlist.group :heading="__('Platform')" class="grid">
            <flux:navlist.item icon="home" :href="route('lawoo')" :current="request()->routeIs('lawoo')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>

    <flux:spacer/>

    <flux:navlist variant="outline">
        <flux:navlist.item icon="folder-git-2" href="https://github.com/lawoo-io/lawoo" target="_blank">
            {{ __('Repository') }}
        </flux:navlist.item>

        <flux:navlist.item icon="book-open-text" href="https://lawoo.io/documentation" target="_blank">
            {{ __('Documentation') }}
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

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
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
