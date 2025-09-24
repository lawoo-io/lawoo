<?php

namespace Modules\Newsletter\Http\Livewire\List;


use Modules\Newsletter\Repositories\CampaignRepository;
use Modules\Web\Http\Livewire\List\BaseListView;

class CampaignListView extends BaseListView
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
    public ?string $modelClass = 'NewsletterCampaign';

    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = CampaignRepository::class;

    /**
     * @var array
     */
    public array $defaultColumns = ['id', 'name'];

    /**
     * @var string
     */
    public string $createViewRoute = 'lawoo.newsletter.campaign.records.create';

    /**
     * @var string
     */
    public string $formViewRoute = 'lawoo.newsletter.campaign.records.view';

    /**
     * Function boot
     */
    public function boot(): void
    {
        $this->title = __t('Campaigns', 'Newsletter');
        parent::boot();
    }

    /**
     * Function setSearchFields
     */
    public static function setSearchFields(): array
    {
        return [
            'name' => __t('Name', 'Newsletter'),
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
            'name' => [
                'label' => __t('Name', 'Newsletter'),
            ],
        ];
    }
}
