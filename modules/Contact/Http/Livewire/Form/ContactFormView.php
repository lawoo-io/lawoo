<?php

namespace Modules\Contact\Http\Livewire\Form;

use Modules\Contact\Repositories\ContactRepository;
use Modules\Web\Http\Livewire\Form\BaseFormView;

class ContactFormView extends BaseFormView
{
    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = ContactRepository::class;

    /**
     * !Required by use a Files
     * @var string
     */
    public string $permissionForShow = 'contact.contact.view';
    public string $permissionForEdit = 'contact.contact.edit';

    /**
     * !Required
     * @var string
     */
    public string $recordsRoute = 'lawoo.contact.list';

    /**
     * !Required only by Create type
     * @var string
     */
    public string $recordRoute = '';

    /**
     * Only by TrackableModel
     * @var bool
     */
    public bool $showMessages = false;

    /**
     * Set Fields function
     */
    public function setFields(): void
    {
        $this->fields = [
            'firstname' => [
                'label' => __t('First Name', 'Contact'),
                'type' => 'input',
                'class' => 'col-span-6',
            ],
            'lastname' => [
                'label' => __t('Last Name', 'Contact'),
                'type' => 'input',
                'class' => 'col-span-6',
                'required' => true,
            ],
            'name' => [
                'label' => __t('Name', 'Contact'),
                'type' => 'hidden',
            ],
            'salutation_id' => [
                'type' => 'select',
                'options' => [],
                'class' => 'lg:col-span-3 col-span-6',
                'placeholder' => __t('--Select Salutation--', 'Contact'),
            ],
            'title_id' => [
                'type' => 'select',
                'options' => [],
                'class' => 'lg:col-span-3 col-span-6',
                'placeholder' => __t('--Select Title--', 'Contact'),
            ],
            'type' => [
                'type' => 'select',
                'options' => [],
                'class' => 'lg:col-span-3 col-span-6',
                'placeholder' => __t('--Select Type--', 'Contact'),
            ],
            'language_id' => [
                'type' => 'select',
                'options' => [],
                'class' => 'lg:col-span-3 col-span-6',
                'placeholder' => __t('--Select Language--', 'Contact'),
            ],
            'group_address' => [
                'label' => __t('Address', 'Contact'),
                'class' => 'col-span-6',
                'fields' => [
                    'street' => [
                        'placeholder' => __t('Street', 'Contact'),
                        'type' => 'input',
                        'class' => 'col-span-10',
                    ],
                    'house_number' => [
                        'placeholder' => __t('No.', 'Contact'),
                        'type' => 'input',
                        'class' => 'col-span-2',
                    ],
                    'street2' => [
                        'placeholder' => __t('Street 2', 'Contact'),
                        'type' => 'input',
                        'class' => 'col-span-12',
                    ],
                    'zip' => [
                        'placeholder' => __t('Zipcode', 'Contact'),
                        'type' => 'input',
                        'class' => 'col-span-4',
                    ],
                    'city' => [
                        'placeholder' => __t('City', 'Contact'),
                        'type' => 'input',
                        'class' => 'col-span-8',
                    ],
                    'country_id' => [
                        'placeholder' => __t('--Select Country--', 'Contact'),
                        'type' => 'select',
                        'class' => 'col-span-6',
                        'options' => []
                    ],
                    'state_id' => [
                        'placeholder' => __t('--Select State--', 'Contact'),
                        'type' => 'select',
                        'class' => 'col-span-6',
                        'options' => []
                    ]
                ],
            ],
            'group_contact' => [
                'label' => __t('Contact Data', 'Contact'),
                'class' => 'col-span-6',
                'fields' => [
                    'phone' => [
                        'placeholder' => __t('Phone', 'Contact'),
                        'type' => 'input',
                        'class' => 'col-span-6',
                    ],
                    'fax' => [
                        'placeholder' => __t('Fax', 'Contact'),
                        'type' => 'input',
                        'class' => 'col-span-6',
                    ],
                    'mobile' => [
                        'placeholder' => __t('Mobile', 'Contact'),
                        'type' => 'input',
                        'class' => 'col-span-12',
                    ],
                    'email' => [
                        'placeholder' => __t('Email', 'Contact'),
                        'type' => 'input',
                        'class' => 'col-span-12',
                    ],
                    'website' => [
                        'placeholder' => __t('Website', 'Contact'),
                        'type' => 'input',
                        'class' => 'col-span-12',
                    ]
                ]
            ],
        ];
    }

}
