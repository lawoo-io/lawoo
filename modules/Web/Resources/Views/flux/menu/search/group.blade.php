{{--
name: 'flux_menu_group',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'heading' => null,
])

@php
    $classes = Flux::classes()
        ->add('-mx-[.3125rem] px-[.3125rem]')
        ->add('[&+&>[data-flux-menu-separator-top]]:hidden [&:first-child>[data-flux-menu-separator-top]]:hidden [&:last-child>[data-flux-menu-separator-bottom]]:hidden')
        ;
@endphp

<div {{ $attributes->class($classes) }} role="group" data-flux-menu-group>

    <?php if ($heading): ?>
    <flux:menu.heading class="pl-0">{{ $heading }}</flux:menu.heading>
    <?php endif; ?>

    {{ $slot }}
</div>

