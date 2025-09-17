{{--
name: 'user_form_header_center_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<flux:button.group>
    @if(intval($cmp->id) === auth()->id())
        <flux:button variant="filled" href="/lawoo/profile/form" size="sm" icon="cog" wire:navigate>{{ __t('Profile Settings', 'User') }}</flux:button>
    @endif
</flux:button.group>
