<?php

namespace Modules\Web\Http\Controllers;

use Flux\Flux;
use FluxPro\FluxPro;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Modules\Core\Abstracts\BaseController;

class ModulesController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:super-admin')->only(['records', 'check']);
    }

    public function records(): View
    {
        return view('modules.web.module.records');
    }

    public function check()
    {
        Artisan::call('module:check');
        return redirect()->route('lawoo.modules');
    }
}
