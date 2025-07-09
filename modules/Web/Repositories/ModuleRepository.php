<?php

namespace Modules\Web\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseRepository;
use Modules\Core\Models\Module;

class ModuleRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Module());
    }

    public function getFilteredData(array $params = []): Builder
    {
        $query = parent::getFilteredData($params);

        $query->whereNotIn('system_name', ['Web']);
        return $query;
    }
}
