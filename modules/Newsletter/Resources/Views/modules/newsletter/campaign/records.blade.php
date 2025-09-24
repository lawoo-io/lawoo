{{--
name: 'campaign_records_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Campaign', 'Newsletter')">
    <livewire:newsletter.list.campaign-list-view/>
</x-web.layouts.sidebar>
