{{--
name: 'c.auth.header',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl">{{ $title }}</flux:heading>
    <flux:subheading>{{ $description }}</flux:subheading>
</div>
