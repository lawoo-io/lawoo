<?php

namespace Modules\Contact\Http\Livewire\List;

use Modules\Contact\Repositories\ContactRepository;
use Modules\Web\Http\Livewire\List\BaseListView;

class ContactListView extends BaseListView
{
    /**
    * !Required
    * @var string
    */
    public ?string $moduleName = 'Contact';

    /**
     * !Required
     * @var string
     */
    public ?string $modelClass = 'Contact';

    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = ContactRepository::class;

    /**
     * @var array
     */
    public array $defaultColumns = ['id', 'name', 'city', 'country_id', 'email', 'phone'];

    /**
     * @var string
     */
    public string $createViewRoute = 'lawoo.contact.list.create';

    /**
     * @var string
     */
    public string $formViewRoute = 'lawoo.contact.list.update';

    /**
     * Function boot
     */
    public function boot(): void
    {
        $this->title = __t('Contacts', 'Contact');
        parent::boot();
    }

    /**
     * Function setSearchFields
     */
    public static function setSearchFields(): array
    {
        return [
            'name' => __t('Name', 'Contact'),
        ];
    }

    /**
     * Function getAvailableColumns
     */
    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'label' => __t('ID', 'Contact')
            ],
            'first_name' => [
                'label' => __t('First Name', 'Contact')
            ],
            'last_name' => [
                'label' => __t('Last Name', 'Contact')
            ],
            'name' => [
                'label' => __t('Name', 'Contact'),
            ],
            'city' => [
                'label' => __t('City', 'Contact'),
            ],
            'country_id' => [
                'label' => __t('Country', 'Contact'),
            ],
            'email' => [
                'label' => __t('Email', 'Contact'),
            ],
            'phone' => [
                'label' => __t('Phone', 'Contact'),
            ],
            'company_id' => [
                'label' => __t('Company', 'Contact'),
            ],
        ];
    }
}
