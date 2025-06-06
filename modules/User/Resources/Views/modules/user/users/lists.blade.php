{{--
name: 'users_lists',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Users', 'User')">
    <livewire:user.list.user-list-view />
</x-web.layouts.sidebar>
