{{--
name: 'country_create_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__('Create Country')">
    <livewire:web.form.country-form-view type="create"/>
</x-web.layouts.sidebar>
