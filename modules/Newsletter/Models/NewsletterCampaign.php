<?php

namespace Modules\Newsletter\Models;

use Modules\Core\Abstracts\BaseModel;


/**
 * Database model description
 *
 * @property int $id
 */

class NewsletterCampaign extends BaseModel
{

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'newsletter_campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

}
