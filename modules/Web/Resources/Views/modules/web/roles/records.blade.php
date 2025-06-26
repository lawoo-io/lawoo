{{--
name: 'role_records',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Users', 'Web')">
    <livewire:web.list.role-list-view/>
</x-web.layouts.sidebar>
