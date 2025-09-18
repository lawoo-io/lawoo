<?php

namespace Modules\Web\Database\Seeders;

use Modules\Web\Models\Company;

class CompanySeeders
{
    public function run(): void
    {
        $data = [
            'name' => 'Mustermann GmbH',
            'is_active' => true,
        ];
        $company = new Company();
        $company->fill($data);
        $company->save();
    }

}
