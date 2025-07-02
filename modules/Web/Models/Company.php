<?php

namespace Modules\Web\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Abstracts\BaseModel;

/**
 * Database model description
 *
 * @property int $id
 */

class Company extends BaseModel
{
    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'street',
        'street_2',
        'zip',
        'city',
        'country_id',
        'parent_id',
        'is_active',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

}
