{{--
name: 'category_create_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Create Category', 'WebsiteBlog')">
    <livewire:website-blog.form.blog-category-form-view type="create"/>
</x-website.layouts.sidebar>
