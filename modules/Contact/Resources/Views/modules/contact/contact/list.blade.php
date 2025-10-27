{{--
name: 'contact_list_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Contacts', 'Contact')">
    <livewire:contact.list.contact-list-view/>
</x-web.layouts.sidebar>
