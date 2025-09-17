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

class Website extends BaseModel
{

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'websites';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'url',
        'meta_title',
        'meta_description',
        'is_active',
        'theme_id',
        'company_id',
        'created_by',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

}
