{{--
name: 'livewire_company_widget',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    @if(count($this->companies))
    <flux:dropdown position="top" align="end">
        <flux:button
            icon:trailing="chevron-down"
            class="cursor-pointer border-none !shadow-none mr-1 !bg-transparent"
            size="sm"
        >
            {{ $this->getSelectedName() }}
        </flux:button>
        <flux:menu>
            @foreach($this->companies as $key => $company)
                <flux:menu.checkbox wire:click="update('{{ $key }}')" :checked="$this->checkChecked($key)">{{ $company }}</flux:menu.checkbox>
            @endforeach
        </flux:menu>
    </flux:dropdown>
    @endif
</div>
