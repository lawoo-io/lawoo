<?php

namespace Modules\Web\Models;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Core\Abstracts\BaseModel;
use Modules\Core\Models\Traits\ClearsCacheOnSave;


/**
 * Database model description
 *
 * @property int $id
 */

class Setting extends BaseModel
{
    use ClearsCacheOnSave;

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'settings_menu_id',
        'module_name',
    ];

    public static function getByKey(string $key)
    {
        try {
            return self::where('key', $key)->firstOrFail()->value;
        } catch (ModelNotFoundException $e) {
            throw new \RuntimeException(__t('Setting not found', 'Web') . ': ' . $key);
        }
    }

}
