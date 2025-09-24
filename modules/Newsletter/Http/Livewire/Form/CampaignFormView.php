<?php

namespace Modules\Newsletter\Http\Livewire\Form;

use Modules\Newsletter\Repositories\CampaignRepository;
use Modules\Web\Http\Livewire\Form\BaseFormView;

class CampaignFormView extends BaseFormView
{
    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = CampaignRepository::class;

    /**
     * !Required
     * @var string
     */
    public string $recordsRoute = 'lawoo.newsletter.campaign.records';

    /**
     * !Required only by Create type
     * @var string
     */
    public string $recordRoute = 'lawoo.newsletter.campaign.records.view';

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
            'name' => [
                'label' => __t('Name', 'Newsletter'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
                'blur' => 'generateSlugFromName'
            ],
            'slug' => [
                'label' => __t('Slug', 'Newsletter'),
                'type' => 'input',
                'class' => 'lg:col-span-6',
            ]
        ];
    }

}
