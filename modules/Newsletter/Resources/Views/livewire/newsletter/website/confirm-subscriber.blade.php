{{--
name: 'livewire_confirm_subscriber',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    @if($status === 'success')
        <flux:callout icon="clock" color="green">
            <flux:callout.heading>{{ __t('Thank you', 'Newsletter') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __t('Your newsletter subscription has been confirmed.', 'Newsletter') }}
            </flux:callout.text>
        </flux:callout>
    @elseif($status === 'resent')
        <flux:callout icon="clock" color="green">
            <flux:callout.heading>{{ __t('Thank you', 'Newsletter') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __t('Please confirm your email address using the link we have just sent you. Your subscription will only be completed after confirmation.', 'Newsletter') }}
            </flux:callout.text>
        </flux:callout>
    @else
        <flux:callout icon="clock" color="red">
            <flux:callout.heading>{{ __t('Link has expired', 'Newsletter') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __t('The confirmation link has expired. Please request a new link to complete your subscription.', 'Newsletter') }}
            </flux:callout.text>
        </flux:callout>
        <flux:button wire:click="sendConfirm" class="mt-4">{{ __t('Request new link', 'Newsletter') }}</flux:button>
    @endif
</div>
