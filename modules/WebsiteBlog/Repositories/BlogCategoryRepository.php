<?php

namespace Modules\WebsiteBlog\Repositories;

use Modules\Website\Repositories\BaseRepository;
use Modules\WebsiteBlog\Models\BlogCategory;

class BlogCategoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new BlogCategory());
    }
}
