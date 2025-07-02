<?php

namespace Modules\Web\Models;

use Modules\Core\Abstracts\BaseModel;
use Modules\Core\Models\Traits\TranslatableModel;


/**
 * Database model description
 *
 * @property int $id
 */

class Country extends BaseModel
{
    use TranslatableModel;

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'countries';

    /**
     * @var string[]
     */
    protected $translatable = ['name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'is_active',
    ];

    public $timestamps = false;

}
