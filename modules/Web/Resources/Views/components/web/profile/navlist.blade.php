{{--
name: 'profile_navlist',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<flux:navlist>
    <flux:navlist.item :href="route('lawoo.profile.form')" :current="request()->routeIs('lawoo.profile.form')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
    <flux:navlist.item :href="route('lawoo.profile.password')" :current="request()->routeIs('lawoo.profile.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
    <flux:navlist.item :href="route('lawoo.profile.appearance')" :current="request()->routeIs('lawoo.profile.appearance')" wire:navigate>{{ __t('Appearance', 'Web') }}</flux:navlist.item>
</flux:navlist>
