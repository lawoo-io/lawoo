<?php

namespace Modules\Web\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class ProfileController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:web.profile.view')->only(['view']);
        $this->middleware(['permission:web.profile.form'])->only(['form']);
        $this->middleware(['permission:web.password.change'])->only(['password']);
        $this->middleware(['permission:web.appearance.view'])->only(['appearance']);
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

    public function appearance()
    {
        return view('modules.web.profile.appearance');
    }
}
