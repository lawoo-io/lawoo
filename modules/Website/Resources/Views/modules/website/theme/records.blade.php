{{--
name: 'theme_records_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Themes', 'Website')">
    <livewire:website.list.theme-list-view/>
</x-website.layouts.sidebar>
