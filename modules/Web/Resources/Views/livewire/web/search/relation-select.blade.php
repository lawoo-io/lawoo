{{--
name: 'livewire_relation_select',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div
    @if($this->hasMore)
    x-data
    x-init="
        requestAnimationFrame(() => {
            const input = $el.querySelector('input[role=\'combobox\']');

            if (input) {
                let debounce;
                input.addEventListener('input', (event) => {
                    clearTimeout(debounce);
                    debounce = setTimeout(() => {
                        $wire.search(event.target.value);
                    }, 300);
                });
            }
        });
    "
    @endif
>
    <flux:select
        wire:model.live="selection"
        variant="listbox"
        :multiple="$multiple"
        :placeholder="$placeholder"
        searchable="search"
        size="sm"
        class="mb-1"
    >
        @foreach($this->options as $optionValue => $optionLabel)
            <flux:select.option :value="$optionValue">{{ $optionLabel }}</flux:select.option>
        @endforeach
    </flux:select>
</div>
