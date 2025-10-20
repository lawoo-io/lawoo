<?php

namespace Modules\WebsiteBlog\Http\Livewire\Website;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\WebsiteBlog\Models\BlogCategory;
use Modules\WebsiteBlog\Models\BlogPost;

class BlogPosts extends Component
{
    use WithPagination;

    protected $posts;

    protected $categories;

    public $perPage = 5;

    public string $blogCategoryPage;

    public int $websiteId;

    public $category;

    public string $blogPage;

    public function mount(string $blogCategoryPage = '', ?int $websiteId = null, ?string $categorySlug = null, string $blogPage = '/blog'): void
    {
        $this->blogCategoryPage = $blogCategoryPage;
        $this->websiteId = $websiteId;
        $this->blogPage = $blogPage;

        if($categorySlug) {
            $this->loadCategory($categorySlug);
        }
    }

    protected function loadCategory(string $categorySlug): void
    {
        $this->category = BlogCategory::where('slug', $categorySlug)->where('website_id', $this->websiteId)->firstOrFail();
        if ($this->category) {
            View::share('title', $this->category->meta_title ?? $this->category->name);
        }
    }

    protected function loadPosts(): Builder
    {
        $query = BlogPost::with(['category'])->where('is_public', true)->where('website_id', $this->websiteId);

        if ($this->category) {
            $query->where('blog_category_id', $this->category->id);
        }

        $query->orderBy('created_at', 'desc');
        return $query;
    }

    protected function loadCategories(): Builder
    {
        return BlogCategory::where('is_public', true)->where('website_id', $this->websiteId)->orderBy('created_at', 'desc');
    }

    public function getCategoryUrl(string $slug): string
    {
        return $this->blogCategoryPage . '/' . $slug;
    }

    public function render()
    {
        return view('livewire.website-blog.website.blog-posts',
            [
                'posts' => $this->loadPosts()->paginate($this->perPage),
                'categories' => $this->loadCategories()->paginate(100),
            ]
        );
    }
}
