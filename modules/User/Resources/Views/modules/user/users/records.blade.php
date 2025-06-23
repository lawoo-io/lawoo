{{--
name: 'users_records',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Users', 'User')">
    @if ($viewType === 'kanban')
    <livewire:user.kanban.user-kanban-view />
    @else
    <livewire:user.list.user-list-view />
    @endif
{{--        <livewire:user.list.user-list-view />--}}
</x-web.layouts.sidebar>
