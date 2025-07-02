{{--
name: 'web_companies_records',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}

<x-web.layouts.sidebar :title="__t('Companies', 'Web')">
    <livewire:web.list.company-list-view/>
</x-web.layouts.sidebar>
