<?php

namespace Modules\Web\Http\Controllers;

use Illuminate\View\View;
use Modules\Core\Abstracts\BaseController;

class CompaniesController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:web.settings.companies.create')->only(['create']);
        $this->middleware('permission:web.settings.companies.show')->only(['records', 'view']);
    }

    public function create()
    {
        return view('modules.web.companies.create');
    }

    public function records(): View
    {
        return view('modules.web.companies.records');
    }

    public function view(): View
    {
        return view('modules.web.companies.view');
    }
}
