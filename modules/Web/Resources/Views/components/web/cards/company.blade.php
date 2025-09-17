{{--
name: 'company_cards',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div class="grid grid-cols-12 gap-4">
    @foreach($items as $item)
        <div class="lg:col-span-4 md:col-span-6 col-span-12">
            <div class="relative p-3 border dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                @php
                $url = route('lawoo.settings.companies.records.view', ['id' => $item->id]);
                @endphp
                <a wire:click="openModal('{{ $modal ? $modal['livewire'] : '' }}', {{ $item->id }}, '{{ $url }}')" class="absolute inset-0 z-5 cursor-pointer" wire:navigate></a>
                <div class="relative z-0">
                    <div class="grid grid-cols-6">
                        @if($item->hasImage())
                            <img src="{{ $item->image()->first()->getThumbnailUrl('web.settings.company.show', 200, 200, 80) }}" class="shrink-0 w-full h-auto object-cover"/>
                        @else
                            <flux:avatar  icon="photo" size="lg" class="col-span-1"/>
                        @endif
                        <div class="col-span-5">
                            <flux:heading level="2">
                                {{ $item->name }}
                            </flux:heading>
                            <flux:text class="mt-2">
                                <p>
                                    {{ $item->street }}
                                </p>
                                <p>
                                    {{ $item->zip }} {{ $item->city }}
                                </p>
                            </flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
