{{--
name: 'page_create_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Create Page', 'Website')">
    <livewire:website.form.page-form-view type="create"/>
</x-website.layouts.sidebar>
