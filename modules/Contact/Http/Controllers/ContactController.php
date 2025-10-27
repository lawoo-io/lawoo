<?php

namespace Modules\Contact\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class ContactController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:contact.contact.view')->only(['list', 'update']);
        $this->middleware('permission:contact.contact.create')->only(['create']);
    }

    public function list()
    {
        return view('modules.contact.contact.list');
    }

    public function create()
    {
        return view('modules.contact.contact.create');
    }

    public function update()
    {
        return view('modules.contact.contact.update');
    }
}
