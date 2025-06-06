<?php

namespace Modules\User\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware('permission:user.users.show')->only('lists');
        $this->middleware('permission:user.users.create')->only('create');
    }

    public function lists()
    {
        return view('modules.user.users.lists');
    }

    public function create()
    {
        return false;
    }
}
