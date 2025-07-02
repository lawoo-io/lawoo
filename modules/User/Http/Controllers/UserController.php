<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;
use Modules\Core\Abstracts\BaseController;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware('permission:user.users.show')->only('records', 'view');
        $this->middleware('permission:user.users.create')->only('create');
    }

    public function records(): View
    {
        $viewType = Cookie::get('User_view_type_' . auth()->id());
        return view('modules.user.users.records', compact('viewType'));
    }

    public function view(): View
    {
        return view('modules.user.users.view');
    }

    public function create()
    {
        return view('modules.user.users.create');
    }
}
