<?php

namespace Modules\Website\Repositories;

use Modules\Website\Models\Website;

class WebsiteRepository extends \Modules\Core\Abstracts\BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Website());
    }

}
