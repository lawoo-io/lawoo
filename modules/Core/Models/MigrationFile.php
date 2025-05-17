<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Database migration file model.
 *
 * @property int $id
 * @property string $path
 * @property bool $migrated
 * @property bool $rollback
 * @property bool $reset
 * @property int $db_model_id
 * @property int $migration_id
 * @property int $module_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static create(array $array)
 */
class MigrationFile extends Model
{

    protected $fillable = [
        'path',
        'db_model_id',
        'module_id',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        /**
         * Delete file if it exists
         */
        static::deleting(function (MigrationFile $migrationFile) {
            if (file_exists(base_path($migrationFile->path))) {
                unlink(base_path($migrationFile->path));
            }
        });
    }


    /**
     * Get the database model this migration file belongs to.
     */
    public function dbModel(): BelongsTo
    {
        return $this->belongsTo(DbModel::class);
    }

    /**
     * Deleted DB records before new created
     * @param int $dbModelId
     * @return void
     */
    public static function deleteBeforeCreate(int $dbModelId): void
    {
        static::where('db_model_id', $dbModelId)
            ->where('migrated', false)
            ->where('rollback', false)
            ->where('reset', false)
            ->get()
            ->each(function ($model) {
                $model->delete();
            });
    }
}
