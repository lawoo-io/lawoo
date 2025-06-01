{{--
name: 'livewire.auth.login',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div class="flex flex-col gap-6">
    <x-web.auth.header :title="__t('Log in to your account', 'Web')" :description="__t('Enter your email and password below to log in', 'Web')" />
    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__t('Email address', 'Web')"
            type="email"
            required=""
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__t('Password', 'Web')"
                type="password"
                required=""
                autocomplete="current-password"
                :placeholder="__t('Password', 'Web')"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    {{ __t('Forgot your password?', 'Web') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__t('Remember me', 'Web')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __t('Log in', 'Web') }}</flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __t('Don\'t have an account?', 'Web') }}
            <flux:link :href="route('register')" wire:navigate>{{ __t('Sign up', 'Web') }}</flux:link>
        </div>
    @endif
</div>
