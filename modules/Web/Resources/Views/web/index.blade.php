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

        <div class="mt-2">
            <p>{{ session('locale') }}</p>
            {{ __t('Welcome to lawoo!', 'Web') }}
        </div>

    </div>

</x-web.layouts.sidebar>
