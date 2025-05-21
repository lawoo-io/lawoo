<?php

namespace Modules\Core\Repositories;

use Modules\Core\Abstracts\BaseRepository;
use Modules\Core\Models\ModuleView;

class ModuleViewRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct(new ModuleView);
    }

    public function isExists(string $name, int $moduleId): bool
    {
        return $this->model->where('name', $name)->where('module_id', $moduleId)->exists();
    }
}
