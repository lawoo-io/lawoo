{{--
name: 'contact_form_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Contact', 'Contact')">
    <livewire:contact.form.contact-form-view/>
</x-web.layouts.sidebar>
