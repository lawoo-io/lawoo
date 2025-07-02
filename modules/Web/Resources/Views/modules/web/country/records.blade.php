{{--
name: 'country_records',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Countries', 'Web')">
    <livewire:web.list.country-list-view/>
</x-web.layouts.sidebar>
