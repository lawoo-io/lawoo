{{--
name: 'index',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <livewire:web.counter/>
    </div>

    <div>
        <p>{{ session('locale') }}</p>
        @_t('Willkommen auf Lawoo!', 'Web')
    </div>
</x-web.layouts.sidebar>
