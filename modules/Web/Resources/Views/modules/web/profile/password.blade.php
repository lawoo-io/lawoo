{{--
name: 'profile_password',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-web.contents.nav-left>
    <x-slot:leftNav>
        <x-web.profile.navlist/>
    </x-slot:leftNav>
    <livewire:web.profile.profile-password/>
</x-web.contents.nav-left>
