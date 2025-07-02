<?php

namespace Modules\Web\Http\Livewire\Form;

use Modules\Web\Repositories\CountryRepository;

class CountryFormView extends BaseFormView
{
    protected string $repositoryClass = CountryRepository::class;

    public string $recordsRoute = 'lawoo.settings.countries';

    public string $recordRoute = 'lawoo.settings.countries.view';

    public function setFields(): void
    {
        $this->fields = [
            'name' => [
                'type' => 'input',
                'label' => __t('Name', 'Web'),
                'class' => 'lg:col-span-6',
            ],
            'is_active' => [
                'type' => 'switch',
                'label' => __t('Active', 'Web'),
                'class' => 'lg:col-span-6',
                'default' => true,
            ],
        ];
    }
}
