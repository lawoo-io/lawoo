{{--
name: 'web_company_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Company', 'Web')">
    <livewire:web.form.company-form-view type="edit" />
</x-web.layouts.sidebar>
