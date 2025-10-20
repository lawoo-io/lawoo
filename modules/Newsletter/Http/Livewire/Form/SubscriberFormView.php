<?php

namespace Modules\Newsletter\Http\Livewire\Form;

use Illuminate\Database\Eloquent\Model;
use Modules\Newsletter\Repositories\CampaignRepository;
use Modules\Newsletter\Repositories\SubscriberRepository;
use Modules\Web\Http\Livewire\Form\BaseFormView;

class SubscriberFormView extends BaseFormView
{
    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = SubscriberRepository::class;

    /**
     * !Required
     * @var string
     */
    public string $recordsRoute = 'lawoo.newsletter.subscriber.records';

    /**
     * !Required only by Create type
     * @var string
     */
    public string $recordRoute = 'lawoo.newsletter.subscriber.records.view';

    /**
     * Only by TrackableModel
     * @var bool
     */
    public bool $showMessages = false;

    public bool $showFirstLastname = false;

    /**
     * Set Fields function
     */
    public function setFields(): void
    {
        $this->fields = [
            'first_name' => [
                'label' => __t('First name', 'Newsletter'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'last_name' => [
                'label' => __t('Last name', 'Newsletter'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'email' => [
                'label' => __t('Email', 'Newsletter'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ],
            'status' => [
                'label' => __t('Status', 'Newsletter'),
                'type' => 'select',
                'options' => [
                    'pending' => __t('Pending', 'Newsletter'),
                    'active' => __t('Active', 'Newsletter'),
                    'unsubscribed' => __t('Unsubscribed', 'Newsletter'),
                    'bounced' => __t('Bounced', 'Newsletter'),
                ],
                'class' => 'lg:col-span-6',
                'disabled' => true,
            ],
            'confirmed_at' => [
                'label' => __t('Confirmed at', 'Newsletter'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
                'disabled' => true,
            ],
            'unsubscribed_at' => [
                'label' => __t('Unsubscribed at', 'Newsletter'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
                'disabled' => true,
            ],
            'campaigns' => [
                'label' => __t('Campaigns', 'Newsletter'),
                'type' => 'checkbox',
                'mode' => 'single',
                'class' => 'lg:col-span-6',
                'options' => $this->campaignOptions(),
            ]
        ];
    }

    protected function campaignOptions(): array
    {
        $campaigns = new CampaignRepository();
        $result = $campaigns->all()->pluck('name', 'id')->toArray();
        return $result;
    }

    protected function loadData(): void
    {
        parent::loadData();
        if ($this->record){
            $this->data['campaigns'] = $this->record->campaigns->pluck('id')->toArray();
        }
    }

    protected function update(): ?Model
    {
        $model = parent::update();
//        $this->syncCampaigns($model);
        return $model;
    }

//    protected function syncCampaigns(Model $model): void
//    {
//        $result = [];
//
//        foreach ($this->data['campaigns'] as $campaign) {
//            $result[$campaign] = ['status' => $model->status, 'subscribed_at' => now()];
//        }
//
//        $model->campaigns()->sync($result);
//    }

}
