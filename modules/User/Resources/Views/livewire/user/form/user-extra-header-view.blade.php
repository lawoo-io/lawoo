{{--
name: 'user_form_extra_header_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    @if (!$cmp->isVerified)
        <flux:button
            variant="primary"
            size="sm"
            class="cursor-pointer"
            wire:click="sendVerificationEmail"
        >
            {{ __t('Send verification Email', 'User') }}
        </flux:button>
    @endif
    <flux:button size="sm" class="cursor-pointer" wire:click="resetPassword">{{ __t('Reset password', 'User') }}</flux:button>
</div>
<div>
    @if ($cmp->isVerified)
        <flux:badge color="green">{{ __t('Activated', 'User') }}</flux:badge>
    @else
        <flux:badge color="red">{{ __t('Unactive', 'User') }}</flux:badge>
    @endif
</div>
