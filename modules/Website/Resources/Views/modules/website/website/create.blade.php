{{--
name: 'website_create',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Create Webstie', 'Website')">
    <livewire:website.form.website-form-view type="create"/>
</x-website.layouts.sidebar>
