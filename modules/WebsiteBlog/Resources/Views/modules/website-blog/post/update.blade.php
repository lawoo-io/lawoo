{{--
name: 'post_update_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Update Post', 'WebsiteBlog')">
    <livewire:website-blog.form.blog-post-form-view/>
</x-website.layouts.sidebar>
