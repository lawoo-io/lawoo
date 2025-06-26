{{--
name: 'role_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Users', 'Web')">
    <livewire:web.form.role-form-view/>
</x-web.layouts.sidebar>
