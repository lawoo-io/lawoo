<?php

namespace Modules\Web\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Abstracts\BaseModel;
use Modules\Core\Models\Traits\ClearsCacheOnSave;
use Modules\Core\Models\Traits\TranslatableModel;


/**
 * Database model description
 *
 * @property int $id
 */

class SettingsMenu extends BaseModel
{
    use TranslatableModel;

    /**
     * Translatable fields
     * @var string[]
     */
    public $translatable = ['name', 'description'];

    public static string $translationIdentifier = 'name';

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'settings_menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'module_name',
        'description',
        'is_active',
        'sequence',
        'icon',
        'middleware',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    public function scopeIsActive($query)
    {
        return $query->where('is_active', true);
    }

}
