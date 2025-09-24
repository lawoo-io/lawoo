<?php

namespace Modules\Newsletter\Repositories;

use Modules\Core\Abstracts\BaseRepository;
use Modules\Newsletter\Models\NewsletterCampaign;

class CampaignRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new NewsletterCampaign());
    }
}
