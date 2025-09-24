<?php

namespace Modules\Newsletter\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class SubscriberController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:newsletter.subscriber.view')->only(['records']);
        $this->middleware('permission:newsletter.subscriber.create')->only(['create']);
    }

    public function records()
    {
        return view('modules.newsletter.subscriber.records');
    }

    public function create()
    {
        return view('modules.newsletter.subscriber.create');
    }

    public function view()
    {
        return view('modules.newsletter.subscriber.form');
    }
}
