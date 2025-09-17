{{--
name: 'layout_records_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Layouts', 'Website')">
    <livewire:website.list.layout-list-view/>
</x-website.layouts.sidebar>
