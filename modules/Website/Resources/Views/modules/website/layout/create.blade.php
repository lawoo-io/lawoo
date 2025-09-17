{{--
name: 'layout_create_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Create Layout', 'Website')">
    <livewire:website.form.layout-form-view type="create"/>
</x-website.layouts.sidebar>
