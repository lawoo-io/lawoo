<?php

namespace Modules\Website\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class ThemeController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:website.theme.view')->only(['records']);
        $this->middleware('permission:website.theme.create')->only(['create']);
    }

    public function records()
    {
        return view('modules.website.theme.records');
    }

    public function create()
    {
        return view('modules.website.theme.create');
    }

    public function view()
    {
        return view('modules.website.theme.view');
    }
}
