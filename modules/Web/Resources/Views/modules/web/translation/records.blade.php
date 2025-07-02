{{--
name: 'translation_records',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Translations', 'Web')">
    <livewire:web.list.translation-list-view/>
</x-web.layouts.sidebar>
