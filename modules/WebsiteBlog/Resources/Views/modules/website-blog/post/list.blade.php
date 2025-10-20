{{--
name: 'post_list_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Posts', 'WebsiteBlog')">
    <livewire:website-blog.list.blog-post-list-view/>
</x-website.layouts.sidebar>
