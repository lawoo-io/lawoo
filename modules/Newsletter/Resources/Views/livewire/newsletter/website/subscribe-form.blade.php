{{--
name: 'livewire_subscribe_form',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div class="mt-4">
    @if($success)
        <flux:callout color="green" class="mt-6 text-center w-full">
            {{ __t('Thank you for subscribing!', 'Newsletter') }}<br class="hidden sm:block"/>
            {{ __t('Please confirm your email address using the link we have just sent you. Your subscription will only be completed after confirmation.', 'Newsletter') }}
        </flux:callout>
    @else
    <form wire:submit.prevent="submit">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
            <flux:field>
                <flux:input wire:model="first_name" placeholder="Vorname" />
                <flux:error name="first_name"/>
            </flux:field>
            <flux:field>
                <flux:input wire:model="last_name" placeholder="Name" />
                <flux:error name="last_name"/>
            </flux:field>
            <flux:field>
                <flux:input wire:model="email" placeholder="E-Mail Adresse" />
                <flux:error name="email"/>
            </flux:field>
            <div class="text-xs">
                <label for="datenschutz">
                    <input type="checkbox" id="datenschutz" wire:model="privacy">
                    Ja, ich möchte E-Mails zu Neuigkeiten und Angeboten von Lawoo erhalten. Hinweise zum Widerruf und zur Datenverarbeitung: <a href="/datenschutz" target="_blank" class="text-cyan-700 underline">Datenschutzerklärung</a>
                </label>
                <flux:error name="privacy"/>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-center gap-x-6">

            <flux:button type="submit" variant="primary" class="cursor-pointer">Jetzt anmelden</flux:button>
            <a href="/kontakt" class="text-sm/6 font-semibold text-gray-900">Kontakt aufnehmen<span aria-hidden="true">→</span></a>
        </div>
    </form>
    @endif
</div>
