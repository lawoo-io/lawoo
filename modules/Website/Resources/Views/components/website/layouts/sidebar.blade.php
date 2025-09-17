{{--
name: 'website_layout_sidebar',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="$title ?? false">
    <x-slot:headerTopRight>
        <livewire:website.widgets.website-selector-widget/>
    </x-slot:headerTopRight>
    {{ $slot ?? '' }}
</x-web.layouts.sidebar>
