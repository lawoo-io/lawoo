<?php

namespace Modules\Web\Repositories;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Abstracts\BaseRepository;
use Modules\Core\Models\Language;

class LanguageRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Language());
    }
}
