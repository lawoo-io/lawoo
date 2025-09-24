{{--
name: 'subscriber_form_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Subscribers', 'Newsletter')">
    <livewire:newsletter.form.subscriber-form-view/>
</x-web.layouts.sidebar>
