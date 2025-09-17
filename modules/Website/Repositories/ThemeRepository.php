<?php

namespace Modules\Website\Repositories;

use Modules\Core\Abstracts\BaseRepository;
use Modules\Website\Models\Theme;

class ThemeRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Theme());
    }
}
