<?php

namespace Modules\Web\Http\Controllers;

use Illuminate\View\View;
use Modules\Core\Abstracts\BaseController;

class RolesController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:web.settings.roles_permissions.show')->only(['records']);
    }

    public function records(): View
    {
        return view('modules.web.roles.records');
    }

    public function view(): View
    {
        return view('modules.web.roles.view');
    }
}
