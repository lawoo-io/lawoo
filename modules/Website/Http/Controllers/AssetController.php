<?php

namespace Modules\Website\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class AssetController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:website.assets.view')->only(['records']);
        $this->middleware('permission:website.assets.create')->only(['create']);
    }

    public function records()
    {
        return view('modules.website.asset.records');
    }

    public function create()
    {
        return view('modules.website.asset.create');
    }

    public function view()
    {
        return view('modules.website.asset.form');
    }
}
