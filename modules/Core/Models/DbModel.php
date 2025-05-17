<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Database model representation for the ERP system.
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static where(string $string, true $true)
 */
class DbModel extends Model
{

    protected $fillable = ['name'];
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class);
    }

    public function dbFields(): HasMany
    {
        return $this->hasMany(DbField::class)->orderBy('id');
    }

    public function migrationFiles(): HasMany
    {
        return $this->hasMany(MigrationFile::class)->orderBy('id', 'desc');
    }

    public function yamlFiles(): BelongsToMany
    {
        return $this->belongsToMany(YamlFile::class, 'yaml_file_db_model');
    }

    /**
     * Create or get a model record and set its changed status.
     *
     * @param string $name Model data
     * @param bool $changed Created or updated model instance
     * @return int $id Model ID
     */
    public static function setChanged(string $name, bool $changed, int $moduleId): int {
        $dbModel = self::firstOrCreate(['name' => $name]);

        if ($dbModel->changed !== $changed) {
            $dbModel->changed = $changed;
            $dbModel->save();
        }

        $dbModel->modules()->syncWithoutDetaching($moduleId);

        return $dbModel->id;
    }

    public function setMigrateOn(): void {
        $this->migrated = false;
        $this->changed = true;
        $this->save();
    }

    public function setMigrateOff(): void
    {
        if ($this->new) $this->new = false;
        $this->migrated = true;
        $this->changed = false;
        $this->save();
    }

    public static function bindModule(array $ids, string $module): void {
        $module = Module::where('system_name', $module)->first();
        if (!$module) return;
        foreach ($ids as $id) {
            $dbModel = self::find($id);
            $dbModel->modules()->syncWithoutDetaching($module->id);
        }
    }
}
