<?php

namespace Modules\User\Http\Controllers;

use Illuminate\View\View;
use Modules\Core\Abstracts\BaseController;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware('permission:user.users.show')->only('lists', 'view');
        $this->middleware('permission:user.users.create')->only('create');
    }

    public function lists(): View
    {
        return view('modules.user.users.lists');
    }

    public function view(): View
    {
        return view('modules.user.users.view');
    }

    public function create()
    {
        return false;
    }
}
