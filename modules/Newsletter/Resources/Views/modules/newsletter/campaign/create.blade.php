{{--
name: 'campaign_create_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Campaign', 'Newsletter')">
    <livewire:newsletter.form.campaign-form-view type="create" />
</x-web.layouts.sidebar>

