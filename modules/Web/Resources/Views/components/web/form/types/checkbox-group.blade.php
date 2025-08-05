{{--
name: 'form_type_checkbox_group',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'options'
])
@if(isset($options['label']))
    <flux:label wire:dirty.class="!text-yellow-500" wire:target="data.{{ $field }}">{{ $options['label'] }}</flux:label>
@endif
<div class="{{ $options['class'] }}">
    @foreach($options['options'] as $group => $items)
        <div class="{{ $options['group_class'] ? $options['group_class'] : false }}">
            <flux:checkbox.group wire:model="data.{{ $field }}" :label="$group"  >
                @foreach($items as $item)
                    <div class="flex items-center">
                        <flux:checkbox :label="$item['name']" :value="$item['id']" />
                        @if (isset($item['description']))
                            <flux:tooltip toggleable>
                                <flux:button icon="information-circle" size="sm" variant="ghost" />
                                <flux:tooltip.content class="max-w-[20rem] space-y-2">
                                    {{ $item['description'] }}
                                </flux:tooltip.content>
                            </flux:tooltip>
                        @endif
                    </div>
                @endforeach
            </flux:checkbox.group>
        </div>
    @endforeach
</div>
