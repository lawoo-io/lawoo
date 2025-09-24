<?php

namespace Modules\Newsletter\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Abstracts\BaseModel;


/**
 * Database model description
 *
 * @property int $id
 */

class NewsletterSubscriber extends BaseModel
{

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'newsletter_subscribers';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
    ];

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(NewsletterCampaign::class, 'campaign_subscriber', 'subscriber_id', 'campaign_id');
    }

}
