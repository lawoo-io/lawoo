{{--
name: 'campaign_form_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.layouts.sidebar :title="__t('Campaign', 'Newsletter')">
    <livewire:newsletter.form.campaign-form-view />
</x-web.layouts.sidebar>
