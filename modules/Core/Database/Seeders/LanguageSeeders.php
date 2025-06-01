<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Language;

class LanguageSeeders extends Seeder
{
    public function run(): void
    {
        $systemDefaultLanguage = config('app.locale', 'de');

        $data = [
            [
                'code' => 'en',
                'name' => 'English',
                'is_active' => true,
                'is_default' => $systemDefaultLanguage === 'en',
            ],
            [
                'code' => 'de',
                'name' => 'Deutsch',
                'is_active' => true,
                'is_default' => $systemDefaultLanguage === 'de',
            ]
        ];

        foreach ($data as $item) {
            $language = new Language();
            $language->fill($item);
            $language->save();
        }
    }

}
