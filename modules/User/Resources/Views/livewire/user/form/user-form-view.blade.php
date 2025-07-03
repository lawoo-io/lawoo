{{--
name: 'livewire_user_form_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@section('formTopLeft')
    @if (!$livewireComponent->data['email_verified_at'])
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
@endsection

@section('formTopRight')
    @if ($livewireComponent->data['email_verified_at'])
        <flux:badge color="green">{{ __t('Activated', 'User') }}</flux:badge>
    @else
        <flux:badge color="red">{{ __t('Unactive', 'User') }}</flux:badge>
    @endif
@endsection

@section('headerCenter')
    @if(intval($livewireComponent->id) === auth()->id())
        <flux:button variant="filled" href="/lawoo/profile/form" size="sm" icon="cog" wire:navigate>{{ __t('Profile Settings', 'User') }}</flux:button>
    @endif
@endsection
