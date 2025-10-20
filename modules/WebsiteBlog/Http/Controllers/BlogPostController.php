<?php

namespace Modules\WebsiteBlog\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class BlogPostController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:website_blog.post.view')->only(['list', 'update']);
        $this->middleware('permission:website_blog.post.create')->only(['create']);
    }

    public function list()
    {
        return view('modules.website-blog.post.list');
    }

    public function create()
    {
        return view('modules.website-blog.post.create');
    }

    public function update()
    {
        return view('modules.website-blog.post.update');
    }
}
