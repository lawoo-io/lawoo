{{--
name: 'nav_left',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar>
    <div class="flex items-start max-md:flex-col">
        <div class="me-10 w-full pb-4 md:w-[220px]">
            {{ $leftNav ?? '' }}
        </div>
        <flux:separator class="md:hidden" />
        <div class="flex-1 self-stretch max-md:pt-6">
            <flux:heading>{{ $heading ?? '' }}</flux:heading>
            <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
            <div class="mt-5 w-full max-w-lg">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-web.layouts.sidebar>
