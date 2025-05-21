<?php

namespace Modules\Core\Repositories;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Abstracts\BaseRepository;
use Modules\Core\Models\DbModel;

class DbModelRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new DbModel);
    }

    /**
     * Check table by name
     * @param string $name
     * @return bool
     */
    public function tableExists(string $name): bool
    {
        $dbModel = $this->model->where('name', $name)->first();
        if (empty($dbModel)) return false;
        return true;
    }
}
