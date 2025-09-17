{{--
name: 'website_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Website Veiw', 'Website')">
    <livewire:website.form.website-form-view />
</x-website.layouts.sidebar>
