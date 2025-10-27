<?php

namespace Modules\WebsiteBlog\Repositories;

use Modules\Core\Abstracts\BaseRepository;
use Modules\WebsiteBlog\Models\BlogPost;

class BlogPostRepository extends BaseReptository
{
    public function __construct()
    {
        parent::__construct(new BlogPost());
    }
}
