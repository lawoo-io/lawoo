<?php

namespace Modules\WebsiteBlog\Http\Livewire\Website;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\WebsiteBlog\Models\BlogPost as Post;

class BlogPost extends Component
{
    protected string $slug;
    protected int $websiteId;
    protected ?Model $post;
    public string $blogCategoryPage;

    public function mount(string $slug, int $websiteId, ?string $blogCategoryPage = null): void
    {
        $this->slug = $slug;
        $this->websiteId = $websiteId;
        $this->blogCategoryPage = $blogCategoryPage;
    }

    protected function loadPost(): ?Model
    {
        return Post::with(['category'])
            ->where('slug', $this->slug)
            ->where('is_active', true)
            ->where('website_id', $this->websiteId)
            ->firstOrFail();

    }

    protected function setTitle(): void
    {
        if ($this->post) {
            View::share('title', $this->post->meta_title ?? $this->post->name);
        }
    }

    public function getCategoryUrl(): string
    {
        return $this->blogCategoryPage . '/' . $this->post->category->slug;
    }

    public function render()
    {
        $this->post = $this->loadPost();
        $this->setTitle();
        return view('livewire.website-blog.website.blog-post',
            ['post' => $this->post]);
    }
}
