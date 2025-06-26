{{--
name: 'livewire_settings',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __t('System Settings', 'Web') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __t('Manage your system settings', 'Web') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="flex items-start max-md:flex-col">
        <div class="me-10 w-full pb-4 md:w-[220px]">
            <flux:navlist>
                @foreach($settingsMenus as $menu)
                    @can($menu['middleware'])
                    <flux:navlist.item
                        href="?id={{ $menu['id'] }}"
                        class="cursor-pointer"
                        :icon="$menu['icon'] ?? false"
                        :current="$menu['id'] === $id"
                        wire:navigate
                    >
                        {{ $menu['name'] }}
                    </flux:navlist.item>
                    @endcan
                @endforeach
            </flux:navlist>
        </div>

        <flux:separator class="md:hidden" />

        <div class="flex-1 self-stretch max-md:pt-6">
            <form wire:submit.prevent="save" clasw="mt-5 w-full max-w-lg ">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    @foreach($fields as $field => $options)
                        <x-web.form.types :field="$field" :options="$options"/>
                    @endforeach
                    <div class="md:col-span-12">
                        <flux:button type="submit" variant="primary" disabled wire:dirty.attr.remove="disabled" class="cursor-pointer">{{ __t('Save', 'Web') }}</flux:button>
                    </div>
                </div>

            </form>
        </div>

    </div>
</div>
