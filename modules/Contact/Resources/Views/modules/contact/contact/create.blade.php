{{--
name: 'contact_create_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Contact', 'Contact')">
    <livewire:contact.form.contact-form-view type="create"/>
</x-web.layouts.sidebar>
