{{--
name: 'blog_category_extra_header_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    @if ($cmp->data['is_public'])
        <flux:badge color="green">{{ __t('Published', 'WebsiteBlog') }}</flux:badge>
    @else
        <flux:badge color="zinc">{{ __t('Unpublished', 'WebsiteBlog') }}</flux:badge>
    @endif
</div>
<div>
    @if(!$cmp->data['is_public'])
        <flux:button wire:click="publish" variant="primary" color="green" size="sm" class="cursor-pointer">{{ __t('To publish', 'WebsiteBlog') }}</flux:button>
    @elseif($cmp->data['is_public'])
        <flux:button wire:click="unpublish" variant="primary" color="red" size="sm" class="cursor-pointer">{{ __t('Unpublish', 'WebsiteBlog') }}</flux:button>
    @endif
</div>
