{{--
name: 'page_form_extra_header_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    @if ($cmp->data['is_public'] && !$cmp->data['is_changed'])
        <flux:badge color="green">{{ __t('Published', 'Website') }}</flux:badge>
    @elseif($cmp->data['is_public'] && $cmp->data['is_changed'])
        <flux:badge color="amber">{{ __t('Modified', 'Website') }}</flux:badge>
    @else
        <flux:badge color="zinc">{{ __t('Unpublished', 'Website') }}</flux:badge>
    @endif
</div>
@if($cmp->data['is_active'])
<div>
    @if(!$cmp->data['is_public'] || $cmp->data['is_changed'])
        <flux:button wire:click="publish" variant="primary" color="green" size="sm" class="cursor-pointer">{{ __t('To publish', 'Website') }}</flux:button>
    @elseif($cmp->data['is_public'])
        <flux:button wire:click="unpublish" variant="primary" color="red" size="sm" class="cursor-pointer">{{ __t('Unpublish', 'Website') }}</flux:button>
    @endif
</div>
@endif
