<?php

namespace Modules\Web\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class SettingsController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:web.settings.show')->only(['index']);
    }

    public function index()
    {
        return view('modules.web.settings.index');
    }
}
