{{--
name: 'livewire_website_selector_widget',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    @if(is_countable($websites) && count($websites))
        <flux:dropdown position="top" align="end">
            <flux:button variant="ghost" icon:trailing="chevron-down" class="cursor-pointer">{{ $this->websites[$this->website_id] }}</flux:button>
            <flux:menu>
                @foreach($websites as $key => $website)
                    <flux:menu.item wire:click="update({{ $key }})" class="cursor-pointer">{{ $website }}</flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    @endif
</div>
