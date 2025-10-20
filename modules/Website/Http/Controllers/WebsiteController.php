<?php

namespace Modules\Website\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class WebsiteController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:website.website.view')->only(['records', 'view']);
        $this->middleware('permission:website.website.create')->only(['create']);
    }

    public function records()
    {
        return view('modules.website.website.records');
    }

    public function create()
    {
        return view('modules.website.website.create');
    }

    public function view()
    {
        return view('modules.website.website.view');
    }
}
