{{--
name: 'livewire_profile_password',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<section class="w-full">
    <form wire:submit="updatePassword" class="my-6 w-full space-y-6">
        <flux:input
            wire:model="current_password"
            :label="__t('Current password', 'Web')"
            type="password"
            required
            autocomplete="current-password"
        />
        <flux:input
            wire:model="password"
            :label="__t('New password', 'Web')"
            type="password"
            required
            autocomplete="new-password"
        />
        <flux:input
            wire:model="password_confirmation"
            :label="__t('Confirm Password', 'Web')"
            type="password"
            required
            autocomplete="new-password"
        />

        <div class="flex items-center gap-4">
            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full">{{ __t('Save', 'Web') }}</flux:button>
            </div>

            <x-web.utils.notification class="me-3 text-green-600" on="password-updated">
                {{ __t('Saved.', 'Web') }}
            </x-web.utils.notification>
        </div>
    </form>
</section>
