<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Abstracts\BaseModel;
use Modules\Web\Models\Company;


/**
 * Database model description
 *
 * @property int $id
 */

class Page extends BaseModel
{

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'url',
        'internal_note',
        'content',
        'meta_title',
        'meta_description',
        'is_active',
        'is_public',
        'is_changed',
        'path',
        'layout_id',
        'company_id',
        'website_id',
    ];

    public function layout(): BelongsTo
    {
        return $this->belongsTo(Layout::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

}
