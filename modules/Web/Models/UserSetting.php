<?php

namespace Modules\Web\Models;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseModel;
use Modules\Core\Models\Traits\ClearsCacheOnSave;


/**
 * Database model description
 */
class UserSetting extends BaseModel
{
    use ClearsCacheOnSave;

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'user_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'user_id',
        'data',
        'key',
        'default',
        'public',
        'is_active',
        'sequence',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('default', 'desc');
        });
    }

}
