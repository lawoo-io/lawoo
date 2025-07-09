{{--
name: 'modal_info',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    <flux:heading level="1" size="lg">{{ $module->name }}</flux:heading>
    <flux:text class="mt-2">{{ $module->short_desc }}</flux:text>
    <flux:text class="mt-2"><strong>{{ __t('Category', 'Web') }}</strong>: {{ $module->moduleCategory->name }}</flux:text>
    <flux:text><strong>{{ __t('System Name', 'Web') }}</strong>: {{ $module->system_name }}</flux:text>
    <flux:text><strong>{{ __t('Version', 'Web') }}</strong>: {{ $module->version }}</flux:text>
    @if($module->version_installed)
        <flux:text><strong>{{ __t('Installed version', 'Web') }}</strong>: {{ $module->version_installed }}</flux:text>
    @endif
    <flux:text class="mt-2"><strong>{{ __t('Author', 'Web') }}</strong>: {{ $module->author }}</flux:text>
    <flux:text><strong>{{ __t('Website', 'Web') }}</strong>: <flux:link href="{{ $module->author_url }}" external="1">{{ $module->author_url }}</flux:link></flux:text>

    @if ($module->enabled && $module->version !== $module->version_installed)
        <flux:callout class="my-3 !p-0" variant="warning" icon="exclamation-circle" heading="{{ __t('Update is available', 'Web') }}"/>
    @endif

    @if ($module->enabled)
        <div class="my-3">
            <flux:button wire:click="update({{ $module->id }})" size="xs">{{ __t('Update', 'Web') }}</flux:button>
            <flux:button wire:click="remove({{ $module->id }})" size="xs" variant="danger">{{ __t('Remove', 'Web') }}</flux:button>
        </div>
    @else
        <div class="my-3">
            <flux:button wire:click="install({{ $module->id }})" size="xs" variant="primary">{{ __t('Install', 'Web') }}</flux:button>
        </div>
    @endif
</div>
