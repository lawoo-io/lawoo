<?php

namespace Modules\Web\Repositories;

use Modules\Core\Abstracts\BaseRepository;
use Modules\Web\Models\Company;

class CompanyRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Company());
    }
}
