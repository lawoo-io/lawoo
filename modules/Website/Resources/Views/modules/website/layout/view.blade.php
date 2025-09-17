{{--
name: 'layout_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Layout', 'Website')">
    <livewire:website.form.layout-form-view/>
</x-website.layouts.sidebar>
