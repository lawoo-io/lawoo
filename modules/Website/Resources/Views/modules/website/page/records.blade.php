{{--
name: 'website_pages_records',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Pages', 'Website')">
    <livewire:website.list.page-list-view/>
</x-website.layouts.sidebar>
