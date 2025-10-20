<?php

namespace Modules\Website\Repositories;

use Modules\Website\Models\Asset;

class AssetRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Asset());
    }
}
