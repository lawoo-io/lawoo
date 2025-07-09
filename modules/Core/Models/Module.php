<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Database\Factories\ModuleFactory;

/**
 * Module model representing a system module in the ERP.
 *
 * @property int $id
 * @property string $name
 * @property string $system_name
 * @property string $short_desc
 * @property int $module_category_id
 * @property string $author
 * @property string $author_url
 * @property string $version
 * @property string $version_installed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static where(string $string, $moduleName)
 */
class Module extends Model
{
    use HasFactory;

    // List of attributes that are mass assignable
    protected $fillable = [
        'name',
        'system_name',
        'short_desc',
        'module_category_id',
        'author',
        'author_url',
        'version',
        'version_installed',
    ];

    public function moduleCategory(): BelongsTo
    {
        return $this->belongsTo(ModuleCategory::class);
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_dependencies', 'module_id', 'depends_on_id');
    }

    public function requiredBy(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_dependencies', 'depends_on_id', 'module_id');
    }

    /**
     * Get models associated with this module.
     */
    public function models(): belongsToMany
    {
        return $this->belongsToMany(Model::class);
    }

    /**
     * Get overrides for this module.
     */
    public function overrides(): HasMany {
        return $this->hasMany(Override::class);
    }

    /**
     * Get views associated with this module.
     */
    public function moduleViews(): HasMany {
        return $this->hasMany(ModuleView::class);
    }

    /**
     * Validates module data.
     *
     * @param array $data
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function validate(array $data): void
    {
        if (!isset($data['id']) && isset($data['system_name'])) {
            $existingModule = static::where('system_name', $data['system_name'])->first();
            if ($existingModule) {
                $data['id'] = $existingModule->id;
            }
        }

        $rules = [
            'name' => 'required|string|max:150',
            'system_name' => 'required|string|max:100|unique:modules,system_name,' . ($data['id'] ?? 'NULL') . ',id',
            'version' => 'required|string|max:100',
            'author' => 'required|string|max:150',
            'author_url' => 'required|string|max:255',
        ];

        // Perform validation and throw an exception if it fails
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    /**
     * Get a new factory instance for this model.
     *
     * @return ModuleFactory
     */
    protected static function newFactory(): ModuleFactory
    {
        return new ModuleFactory();
    }

    /**
     * Validate and create or update a module.
     *
     * @param array $values Input data
     * @param string $moduleName Module system name
     * @return static Created or updated module
     */
    public static function createOrUpdateValidate(array $values, string $moduleName): void
    {

        $values['system_name'] = $moduleName;

        // Run validation
        static::validate($values);

        // Extract the unique key used for matching existing records
        $where = ['system_name' => $moduleName];

        // Get Category ID
        if (array_key_exists('category', $values)) {
            $values['module_category_id'] = static::getModuleCategoryIdByName($values['category']);
        }

        // Perform update or create operation
        static::updateOrCreate($where, $values);
    }

    public static function attachDependency(array $values, string $moduleName): void
    {
        if (!key_exists('depends', $values)) return;

        $ids = [];
        foreach ($values['depends'] as $depend) {
            $id = static::where('system_name', $depend)->value('id');
            if ($id !== null) {
                $ids[] = $id;
            } else {
                throw new \RuntimeException("Dependency module '$depend' was not found.");
            }
        }

        if ($ids) {
            $module = static::where('system_name', $moduleName)->first();
            if ($module) $module->dependencies()->sync($ids);
        }
    }

    /**
     * Get module category ID by name.
     *
     * @param string $name Category name
     * @return int|null Category ID or null if not found
     */
    public static function getModuleCategoryIdByName(string $name): ?int {
        return ModuleCategory::where('name', $name)->value('id');
    }

    public function collectDependenciesForInstall(array &$visited = []): array
    {
        $result = [];

        foreach ($this->dependencies as $dependency) {
            if (!$dependency->enabled && !in_array($dependency->id, $visited)) {
                $visited[] = $dependency->id;

                $subDeps = $dependency->collectDependenciesForInstall($visited);

                $result = array_merge($result, $subDeps);
                $result[] = $dependency;
            }
        }

        return $result;
    }

    public static function getDependenciesForInstall(string $moduleName): array
    {
        $module = static::where('system_name', $moduleName)->first();

        if ($module) {
            $visited = [];
            $dependencies = $module->collectDependenciesForInstall($visited);

            return array_unique($dependencies, SORT_REGULAR);
        }

        return [];
    }

    public function collectRequiredByDependents(array &$visited = []): array
    {
        $result = [];

        foreach ($this->requiredBy as $dependent) {
            if ($dependent->enabled && !in_array($dependent->id, $visited)) {
                $visited[] = $dependent->id;

                $subDependents = $dependent->collectRequiredByDependents($visited);

                $result = array_merge($result, $subDependents);
                $result[] = $dependent;
            }
        }

        return $result;
    }

    public static function getRequiredByDependents(string $moduleName): array
    {
        $module = static::where('system_name', $moduleName)->first();

        if ($module) {
            $visited = [];
            $dependents = $module->collectRequiredByDependents($visited);

            return array_unique($dependents, SORT_REGULAR);
        }

        return [];
    }

}
