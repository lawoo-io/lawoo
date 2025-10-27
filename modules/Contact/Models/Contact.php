<?php

namespace Modules\Contact\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Abstracts\BaseModel;
use Modules\Core\Models\Traits\HasFiles;


/**
 * Database model description
 *
 * @property int $id
 */

class Contact extends BaseModel
{

    use SoftDeletes, HasFiles;

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'salutation_id',
        'title_id',
        'first_name',
        'last_name',
    ];

}
