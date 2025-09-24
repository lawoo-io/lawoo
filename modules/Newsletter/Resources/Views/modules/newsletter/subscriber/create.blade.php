{{--
name: 'subscriber_create_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Add Subscriber', 'Newsletter')">
    <livewire:newsletter.form.subscriber-form-view type="create"/>
</x-web.layouts.sidebar>
