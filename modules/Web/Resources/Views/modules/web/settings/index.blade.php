{{--
name: 'settings_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Settings', 'Web')">
    <livewire:web.settings.settings />
</x-web.layouts.sidebar>
