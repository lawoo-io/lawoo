{{--
name: 'livewire_profile_form',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __t('Profile Settings', 'Web') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __t('Manage your profile and account settings', 'Web') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    <form wire:submit="submit" class="my-6 w-full space-y-6">
        <flux:input wire:model="name" type="text" :label="__t('Name', 'Web')" autofocus autocomplete="name"/>

        <div>
            <flux:input wire:model="email" type="email" :label="__t('Email', 'Web')" autofocus />
            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                <div>
                    <flux:text class="mt-4">
                        {{ __t('Your email address is unverified.') }}

                        <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                            {{ __t('Click here to re-send the verification email.') }}
                        </flux:link>
                    </flux:text>

                    @if (session('status') === 'verification-link-sent')
                        <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                            {{ __t('A new verification link has been sent to your email address.') }}
                        </flux:text>
                    @endif
                </div>
            @endif
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full">{{ __t('Save', 'Web') }}</flux:button>
            </div>

            <x-web.utils.notification on="profile-updated" class="text-green-600">
                {{ __t('Saved', 'Web') }}
            </x-web.utils.notification>
        </div>
    </form>
</section>
