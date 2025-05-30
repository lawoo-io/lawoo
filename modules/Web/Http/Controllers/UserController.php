<?php

namespace Modules\Web\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:user.profile.view')->only(['view']);
        $this->middleware(['permission:user.profile.form'])->only(['form']);
        $this->middleware(['permission:user.password.change'])->only(['password']);
    }

    /**
     * Edit own profile
     * Livewire components handle all the logic
     */
    public function profile()
    {
        return view('modules.web.profile.form');
    }

    public function password()
    {
        return view('modules.web.profile.password');
    }

}
