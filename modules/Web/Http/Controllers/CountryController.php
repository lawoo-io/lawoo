<?php

namespace Modules\Web\Http\Controllers;

use Illuminate\View\View;
use Modules\Core\Abstracts\BaseController;

class CountryController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:web.settings.country_language.show')->only(['records']);
        $this->middleware('permission:web.settings.country_language.edit')->only(['view']);
        $this->middleware('permission:web.settings.country_language.create')->only(['create']);
    }

    public function records(): View
    {
        return view('modules.web.country.records');
    }

    public function create(): View
    {
        return view('modules.web.country.create');
    }

    public function view(): View
    {
        return view('modules.web.country.view');
    }
}
