<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * YAML file model for the ERP system.
 *
 * @property int $id
 * @property string $name
 * @property string $path
 * @property int $db_model_id
 * @property int $module_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class YamlFile extends Model
{
    /**
     * Get the database model this YAML file belongs to many.
     */
    public function dbModels(): BelongsToMany
    {
        return $this->belongsToMany(DbModel::class, 'yaml_file_db_model');
    }

    public function modules(): HasMany
    {
        return $this->HasMany(Module::class);
    }

    public static function updateOrCreate(object $file, array $parsed, string $module): int
    {
        $module = Module::where('system_name', $module)->first();

        $path = str_replace(base_path() . '/', '', $file->getPathname());

        $yamlFile = YamlFile::where('path', '=', $path)->first();

        $changed = false;
        if (!$yamlFile) {
            $yamlFile = app(YamlFile::class);
            $yamlFile->path = $path;
            $yamlFile->file_modified_at = $file->getMTime();
            $yamlFile->file_hash = hash_file('md5', $file->getPathname());
            $yamlFile->module_id = $module->id;
            $yamlFile->save();
            $changed = true;
        }

        if ($yamlFile->file_modified_at < $file->getMTime() || $yamlFile->file_hash !== hash_file('md5', $file->getPathname())) {
            $yamlFile->file_modified_at = $file->getMTime();
            $yamlFile->file_hash = hash_file('md5', $file->getPathname());
            $yamlFile->save();
            $changed = true;
        }

        if (!$changed) return 0;

        $ids = [];

        foreach ($parsed as $key => $value) {
            $ids[] = DbModel::setChanged($key, true, $module->id);
        }

        $yamlFile->dbModels()->syncWithoutDetaching($ids);

        DbModel::bindModule($ids, $module);

        return 1;
    }

}
