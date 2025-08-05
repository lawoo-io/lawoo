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
            'phone_code' => [
                'type' => 'input',
                'label' => __t('Phone Code', 'Web'),
                'class' => 'lg:col-span-6',
            ],
            'iso2' => [
                'type' => 'input',
                'placeholder' => __t('EN', 'Web'),
                'label' => __t('Country code', 'Web'),
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

    public function setRules(): void
    {
        $this->rules = [
            'data.name' => 'required',
            'data.phone_code' => 'required|min:1|max:2',
            'data.iso2' => 'required|min:2|max:2',
        ];
    }
}
