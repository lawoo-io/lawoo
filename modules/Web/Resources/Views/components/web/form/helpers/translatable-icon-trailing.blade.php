{{--
name: 'translatable_icon_trailing',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props(['field'])
@if (isset($this->translatableFields) && count($this->locales) > 1 && in_array($field, $this->translatableFields))
    <x-slot name="iconTrailing">
        @if ($this->type === 'edit')
            <flux:dropdown position="bottom" align="end">
                <flux:button size="xs" class="cursor-pointer border-none !text-gray-400">{{ $this->locale }}</flux:button>
                <flux:menu>
                    @foreach($this->locales as $key => $value)
                        <flux:menu.item wire:click="setLocale('{{ $key }}')" :current="$key === $this->locale">{{ $value }}</flux:menu.item>
                    @endforeach
                </flux:menu>
            </flux:dropdown>
        @else
            <span class="mr-1.5">{{ $this->defaultLocale }}</span>
        @endif
    </x-slot>
@endif
