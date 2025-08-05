<?php

namespace Modules\Web\Database\Seeders;

use Modules\Web\Models\CommunicationType;

class CommunicationTypeSeeders
{
    public function run(): void
    {
        $data = $this->getData();
        foreach ($data as $type) {
            $com = new CommunicationType();
            $com->fill($type);
            $com->save();
        }
    }

    protected function getData(): array
    {
        return [
            [
                'slug' => 'phone',
                'name' => 'Phone',
                'module' => 'Web',
                'is_system' => 1,
                'is_active' => 1,
                'sequence' => 10,
                'requires_country' => true,
                'supports_verifications' => true,
                'validation_rules' => [
                    'pattern' => '^\\+?[1-9]\\d{1,14}$',
                    'required_length' => ['min' => 7, 'max' => 15],
                    'country_aware' => true
                ],
                'formatting_rules' => [
                    'international' => true,
                    'display_format' => '+{country} {area} {number}',
                    'storage_format' => 'e164'
                ]
            ],
            [
                'slug' => 'email',
                'name' => 'Email',
                'module' => 'Web',
                'is_system' => 1,
                'is_active' => 1,
                'sequence' => 20,
            ],
            [
                'slug' => 'fax',
                'name' => 'Fax',
                'module' => 'Web',
                'is_system' => 1,
                'is_active' => 1,
                'sequence' => 30,
            ],
            [
                'slug' => 'website',
                'name' => 'Website',
                'module' => 'Web',
                'is_system' => 1,
                'is_active' => 1,
                'sequence' => 40,
            ]
        ];
    }
}
