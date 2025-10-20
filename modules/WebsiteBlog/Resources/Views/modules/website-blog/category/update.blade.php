{{--
name: 'category_update_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<x-website.layouts.sidebar :title="__t('Update Category', 'WebsiteBlog')">
    <livewire:website-blog.form.blog-category-form-view/>
</x-website.layouts.sidebar>
