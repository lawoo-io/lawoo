{{--
name: 'company_form_create',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Create company', 'Web')">
    <livewire:web.form.company-form-view type="create" />
</x-web.layouts.sidebar>
