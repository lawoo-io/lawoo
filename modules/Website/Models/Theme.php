<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Abstracts\BaseModel;
use Modules\Core\Models\Traits\HasFiles;


/**
 * Database model description
 *
 * @property int $id
 */

class Theme extends BaseModel
{
    use HasFiles;

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'themes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'system_name',
        'short_description',
        'description',
        'author_name',
        'author_website',
    ];

}
