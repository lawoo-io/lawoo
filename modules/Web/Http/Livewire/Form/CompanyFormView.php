<?php

namespace Modules\Web\Http\Livewire\Form;

use Modules\Web\Models\Country;

class CompanyFormView extends BaseFormView
{
    protected string $repositoryClass = 'Modules\\Web\Repositories\\CompanyRepository';

    public string $recordsRoute = 'lawoo.settings.companies.records';

    public string $recordRoute = 'lawoo.settings.companies.records.view';

//    public bool $showMessages = true;

    public function setFields(): void
    {
        $this->fields = [
            'name' => [
                'label' => __t('Name', 'Web'),
                'type' => 'input',
                'class' => 'lg:col-span-12',
            ],
            'street' => [
                'label' => __t('Street, No', 'Web'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'street_2' => [
                'label' => __t('Street 2', 'Web'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'zip' => [
                'label' => __t('Zip', 'Web'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'city' => [
                'label' => __t('City', 'Web'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'country_id' => [
                'label' => __t('Country', 'Web'),
                'type' => 'select',
                'class' => 'lg:col-span-6',
                'options' => Country::active()->get()->pluck('name', 'id'),
            ],
            'is_active' => [
                'label' => __t('Active', 'Web'),
                'type' => 'switch',
                'class' => 'lg:col-span-6',
                'default' => true,
            ]
        ];
    }

    public function save(): void
    {
        parent::save();
        $this->dispatch('companies-refresh');
    }

    public function deleteRecord(): void
    {
        parent::deleteRecord();
        $this->dispatch('companies-refresh');
    }

}
