{{--
name: 'user_create_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Create user', 'User')">
    <livewire:user.form.user-form-view type="create"/>
</x-web.layouts.sidebar>
