<?php

namespace Modules\Web\Http\Api;

use Illuminate\Support\Facades\Request;
use Modules\Core\Abstracts\BaseController;

class ProfileController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');  // API Authentication
        $this->middleware('permission:user.profile.edit')->only(['update']);
        $this->middleware('permission:user.password.change')->only(['changePassword']);
    }

    public function view()
    {
        //
    }

    public function update(Request $request)
    {
        //
    }

    public function changePassword(Request $request)
    {
        //
    }
}
