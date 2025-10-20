<?php

namespace Modules\WebsiteBlog\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class BlogCategoryController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:website_blog.category.view')->only(['list', 'update']);
        $this->middleware('permission:website_blog.category.create')->only(['create']);
    }

    public function list()
    {
        return view('modules.website-blog.category.list');
    }

    public function create()
    {
        return view('modules.website-blog.category.create');
    }

    public function update()
    {
        return view('modules.website-blog.category.update');
    }
}
