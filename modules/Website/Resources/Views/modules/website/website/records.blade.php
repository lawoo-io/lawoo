{{--
name: 'website_records',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Websties', 'Website')">
    <livewire:website.list.website-list-view />
</x-website.layouts.sidebar>
