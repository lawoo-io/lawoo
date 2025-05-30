<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Database field model representing a field in a database table.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $hint
 * @property bool $changed
 * @property bool $migrated
 * @property int $db_model_id
 * @property int|null $module_id
 * @property string|null $params
 * @property string|null $new_params
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static where(string $string, true $true)
 */
class DbField extends Model
{
    protected $table = 'db_fields';

    protected $fillable = [
        'name',
        'description',
        'hint',
        'params',
        'new_params',
        'db_model_id',
        'module_id',
    ];

    /**
     * Get the database model this field belongs to.
     */
    public function dbModel(): BelongsTo
    {
        return $this->belongsTo(DbModel::class);
    }

    /**
     * Get the module this field belongs to.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Set migrated off
     */
    public function setMigrateOff(): void
    {
        if(!$this->created) $this->created = true;
        $this->changed = false;
        $this->migrated = true;
        if ($this->new_params) {
            $this->params = $this->new_params;
            $this->new_params = null;
        }
        $this->save();
    }
}
