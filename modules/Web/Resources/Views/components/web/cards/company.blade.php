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
                <a href="{{ route('lawoo.settings.companies.records.view', [$item->id]) }}" class="absolute inset-0 z-5 cursor-pointer" wire:navigate></a>
                <div class="pointer-events-auto z-10 absolute top-1 right-1.5">
                    <flux:dropdown position="bottom" align="end" class="ml-2">
                        <flux:button variant="ghost" size="xs" icon="ellipsis-vertical" inset="top right bottom" />
                        <flux:menu>
                            @can('web.settings.company.delete')
                                <flux:menu.item
                                    class="size-6 cursor-pointer"
                                    wire:click="deleteSub({{ $item->id }})"
                                    wire:confirm="{{ __t('Are you sure you want to delete the selected items?', 'Web') }}"
                                >
                                    <flux:icon.trash class="size-4 mr-2 text-red-600" />
                                    {{ __t('Remove', 'Web') }}
                                </flux:menu.item>
                            @endcan
                        </flux:menu>
                    </flux:dropdown>
                </div>
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
