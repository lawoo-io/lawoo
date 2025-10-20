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
    @vite(['resources/js/web/modalUrl.js', 'resources/js/web/codemirror.js'])
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 test">
<flux:sidebar id="sidebar"
              sticky
              collapsible
              class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.header>
        <flux:sidebar.brand
            href="{{ route('lawoo.dashboard') }}"
            logo="{{ Vite::asset('resources/images/web/logo/b.png') }}"
            logo:dark="{{ Vite::asset('resources/images/web/logo/w.png') }}"
            name="Lawoo"
            wire:navigate
        />
        <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
    </flux:sidebar.header>

    <!-- Navigation Left -->
    <livewire:web.nav.nav-left/>

    <flux:spacer/>

    <flux:sidebar.nav>
        <flux:sidebar.item icon="folder-git-2" href="https://github.com/lawoo-io/lawoo" target="_blank">
            {{ __t('Repository', 'Web') }}
        </flux:sidebar.item>

        <flux:sidebar.item icon="book-open-text" href="https://lawoo.io/documentation" target="_blank">
            {{ __t('Documentation', 'Web') }}
        </flux:sidebar.item>
    </flux:sidebar.nav>

</flux:sidebar>
<flux:header class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 flex items-center !pl-1 !pr-1">

    <livewire:web.nav.nav-top/>

    <flux:spacer />

    @if(isset($headerTopRight))
        {{ $headerTopRight }}
    @endif

    <livewire:web.widgets.company-widget/>

    <flux:dropdown position="top" align="end">
        <flux:profile
            circle
            :initials="auth()->user()->initials()"
            class="shrink-0 cursor-pointer"
            :iconTrailing="false"
        />
        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <span class="p-1 bg-gray-200 dark:bg-gray-700 rounded">{{ auth()->user()->initials() }}</span>
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
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                    {{ __t('Logout', 'Web') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

<flux:main class="!p-6">
    {{ $slot }}
</flux:main>

<livewire:web.modal.base-modal-view />
<livewire:web.modal.modal-stack />
@fluxScripts
@persist('toast')
<flux:toast.group position="top right" expanded>
    <flux:toast class="mt-12" />
</flux:toast.group>
@endpersist
@stack('scripts')
</body>

</html>
