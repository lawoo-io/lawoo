{{--
name: 'language_records',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Languages', 'Web')">
    <livewire:web.list.language-list-view/>
</x-web.layouts.sidebar>

