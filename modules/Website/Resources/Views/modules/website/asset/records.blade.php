{{--
name: 'asset_records_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Assets', 'Website')">
    <livewire:website.list.asset-list-view/>
</x-website.layouts.sidebar>
