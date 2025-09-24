<?php

namespace Modules\Newsletter\Http\Controllers;

use Modules\Core\Abstracts\BaseController;

class CampaignController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:newsletter.campaign.view')->only(['records']);
        $this->middleware('permission:newsletter.campaign.create')->only(['create']);
    }

    public function records()
    {
        return view('modules.newsletter.campaign.records');
    }

    public function create()
    {
        return view('modules.newsletter.campaign.create');
    }

    public function view()
    {
        return view('modules.newsletter.campaign.form');
    }
}
