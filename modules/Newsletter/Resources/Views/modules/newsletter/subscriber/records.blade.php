{{--
name: 'subscriber_records_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Subscribers', 'Newsletter')">
    <livewire:newsletter.list.subscriber-list-view/>
</x-web.layouts.sidebar>
