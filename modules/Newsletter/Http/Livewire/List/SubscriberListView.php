<?php

namespace Modules\Newsletter\Http\Livewire\List;


use Modules\Newsletter\Repositories\SubscriberRepository;
use Modules\Web\Http\Livewire\List\BaseListView;

class SubscriberListView extends BaseListView
{
    /**
    * !Required
    * @var string
    */
    public ?string $moduleName = 'Newsletter';

    /**
     * !Required
     * @var string
     */
    public ?string $modelClass = 'NewsletterSubscriber';

    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = SubscriberRepository::class;

    /**
     * @var array
     */
    public array $defaultColumns = ['id', 'email'];

    /**
     * @var string
     */
    public string $createViewRoute = 'lawoo.newsletter.subscriber.records.create';

    /**
     * @var string
     */
    public string $formViewRoute = 'lawoo.newsletter.subscriber.records.view';

    /**
     * Function boot
     */
    public function boot(): void
    {
        $this->title = __t('Subscribers', 'Newsletter');
        parent::boot();
    }

    /**
     * Function setSearchFields
     */
    public static function setSearchFields(): array
    {
        return [
            'email' => __t('Email', 'Newsletter'),
        ];
    }

    /**
     * Function getAvailableColumns
     */
    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'label' => __t('ID', 'Newsletter')
            ],
            'email' => [
                'label' => __t('Email', 'Newsletter'),
            ],
            'first_name' => [
                'label' => __t('First Name', 'Newsletter'),
            ],
            'last_name' => [
                'label' => __t('Last Name', 'Newsletter'),
            ]
        ];
    }
}
