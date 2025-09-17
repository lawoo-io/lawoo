<?php

namespace Modules\Website\Repositories;

use Modules\Core\Abstracts\BaseRepository;
use Modules\Website\Models\Website;

class WebsiteRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Website());
    }

}
