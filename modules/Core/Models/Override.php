<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Override model for managing class overrides in the ERP system.
 *
 * @property int $id
 * @property string $original_class
 * @property string $override_class
 * @property int $module_id
 * @property bool $active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Override extends Model
{
    protected $fillable = [
        'original_class',
        'override_class',
        'module_id',
        'active',
    ];

    /**
     * Get the module this override belongs to.
     */
    public function module(): BelongsTo {
        return $this->belongsTo(Module::class);
    }
}
