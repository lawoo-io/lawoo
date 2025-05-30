{{--
name: 'profile_navlist',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<flux:navlist>
    <flux:navlist.item :href="route('profile.form')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
    <flux:navlist.item :href="route('profile.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
{{--    <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>--}}
</flux:navlist>
