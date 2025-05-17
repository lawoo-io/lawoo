<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\ModuleCategory;

class ModuleCategorySeeders extends Seeder
{
    public function run(): void {

        $data = [
            [
                'name' => 'Basis',
                'slug' => 'basis',
            ],
            [
                'name' => 'Vertrieb',
                'slug' => 'vertrieb',
            ],
            [
                'name' => 'Einkauf',
                'slug' => 'einkauf',
            ],
            [
                'name' => 'Lager',
                'slug' => 'lager',
            ],
            [
                'name' => 'Finanzen',
                'slug' => 'finanzen',
            ],
            [
                'name' => 'Produktion',
                'slug' => 'produktion',
            ],
            [
                'name' => 'Berichte',
                'slug' => 'berichte',
            ],
            [
                'name' => 'Schnittstellen',
                'slug' => 'schnittstellen',
            ],
            [
                'name' => 'Website',
                'slug' => 'website',
            ],
            [
                'name' => 'Integraion',
                'slug' => 'integraion',
            ],
            [
                'name' => 'KI',
                'slug' => 'ki',
            ]
        ];

        foreach ($data as $item) {
            $mc = new ModuleCategory();
            $mc->fill($item);
            $mc->save();
        }
    }
}
