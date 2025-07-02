<?php

namespace Modules\Web\Http\Controllers;

use Illuminate\View\View;
use Modules\Core\Abstracts\BaseController;

class TranslationsController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:web.settings.country_language.show')->only(['records', 'view']);
    }

    public function records(): View
    {
        return view('modules.web.translation.records');
    }

    public function view(): View
    {
        return view('modules.web.translation.view');
    }
}
