{{--
name: 'profile_appearance',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.contents.nav-left :heading="__t('Profile Appearance', 'Web')" :subheading="__t('Update the appearance settings for your account', 'Web')">
    <x-slot:leftNav>
        <x-web.profile.navlist/>
    </x-slot:leftNav>
    <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
        <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
        <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
        <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
    </flux:radio.group>
</x-web.contents.nav-left>
