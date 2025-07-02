<?php

namespace Modules\Web\Repositories;

use Modules\Core\Abstracts\BaseRepository;
use Modules\Web\Models\Country;

class CountryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Country());
    }
}
