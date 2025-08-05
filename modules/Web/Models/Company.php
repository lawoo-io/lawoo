<?php

namespace Modules\Web\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Abstracts\BaseModel;
use Modules\Core\Models\Traits\HasFiles;

/**
 * Database model description
 *
 * @property int $id
 */

class Company extends BaseModel
{
    use HasFiles;

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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

}
