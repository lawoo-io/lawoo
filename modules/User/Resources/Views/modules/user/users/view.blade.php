{{--
name: 'user_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('User', 'User')">
    <livewire:user.form.user-form-view />
</x-web.layouts.sidebar>
