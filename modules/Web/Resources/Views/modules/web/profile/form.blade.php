{{--
name: 'profile_form',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}

<x-web.contents.nav-left :heading="__t('Profile Settings', 'Web')" :subheading="__t('Manage your profile and account settings', 'Web')">
    <x-slot:leftNav>
        <x-web.profile.navlist/>
    </x-slot:leftNav>
    <livewire:web.profile.profile-form />
</x-web.contents.nav-left>
