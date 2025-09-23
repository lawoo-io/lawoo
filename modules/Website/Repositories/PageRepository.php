<?php

namespace Modules\Website\Repositories;

use Modules\Website\Models\Page;

class PageRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Page());
    }
}
