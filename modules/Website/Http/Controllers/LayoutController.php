<?php

namespace Modules\Website\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class LayoutController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:website.layouts.view')->only(['records']);
        $this->middleware('permission:website.layouts.create')->only(['create']);
    }

    public function records()
    {
        return view('modules.website.layout.records');
    }

    public function create()
    {
        return view('modules.website.layout.create');
    }

    public function view()
    {
        return view('modules.website.layout.view');
    }
}
