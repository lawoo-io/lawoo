<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Abstracts\BaseModel;


/**
 * Database model description
 *
 * @property int $id
 */

class Asset extends BaseModel
{

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'assets';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'path',
        'type',
        'internal_note',
        'content',
        'is_active',
        'is_public',
        'auto_public',
        'is_changed',
        'company_id',
        'theme_id',
        'website_id',
    ];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

}
