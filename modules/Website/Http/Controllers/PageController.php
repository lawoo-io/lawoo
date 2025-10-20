<?php

namespace Modules\Website\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class PageController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:website.pages.view')->only(['records', 'view']);
        $this->middleware('permission:website.pages.create')->only(['create']);
    }

    public function records()
    {
        return view('modules.website.page.records');
    }

    public function create()
    {
        return view('modules.website.page.create');
    }

    public function view()
    {
        return view('modules.website.page.view');
    }
}
