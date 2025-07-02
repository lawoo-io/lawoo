<?php

namespace Modules\Web\Repositories;

use Modules\Core\Abstracts\BaseRepository;
use Modules\Core\Models\ModuleUiTranslation;

class ModuleUiTranslationRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new ModuleUiTranslation());
    }
}
