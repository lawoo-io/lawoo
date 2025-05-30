<?php

namespace Modules\Core\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Core\Services\Resources\OverrideViews;

/**
 * Module view model representing a view in the ERP system.
 *
 * @property int $id
 * @property string $name
 * @property string|null $override_name
 * @property string $base
 * @property int $module_id
 * @property string $path
 * @property int|null $parent_id
 * @property int $priority
 * @property \Carbon\Carbon|null $file_modified_at
 * @property bool $content_changed
 * @property string|null $file_hash
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ModuleView extends Model
{
    protected $fillable = [
        'name',
        'override_name',
        'base',
        'module_id',
        'path',
        'parent_id',
        'priority',
        'file_modified_at',
        'content_changed',
    ];

    protected $casts = [
        'file_modified_at' => 'datetime',
    ];

    /**
     * Get the module this view belongs to.
     */
    public function module() {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get child views that override this view.
     */
    public function children() {
        return $this->hasMany(ModuleView::class, 'parent_id');
    }

    /**
     * Get the parent view this view overrides.
     */
    public function parent() {
        return $this->belongsTo(ModuleView::class, 'parent_id');
    }

    /**
     * Save or update view metadata.
     *
     * @param array $metaData View metadata
     * @param string $fileTime File modification timestamp
     * @param string $fileHash File hash
     * @param string $moduleName Module system name
     * @throws \RuntimeException When module or parent view not found
     */
    public static function saveOrUpdateMetaData(array $metaData, string $fileTime, string $fileHash, string $moduleName): void
    {
        $module = Module::where('system_name', $moduleName)->where('enabled', true)->first();

        if (is_null($module)) {
            throw new \RuntimeException("Module '{$moduleName}' not found or not installed in the database.");
        }

        static::checkPaths($module);

        $view = self::firstOrNew(['path' => $metaData['path'], 'name' => $metaData['name']]);

        if (!$view->file_modified_at || $view->file_modified_at->timestamp !== $fileTime) {
            $content_changed = false;

            if (!$view->file_hash != $fileHash) {
                $content_changed = true;
            }

            $parentView = false;

            if ($metaData['override_name']) {
                $override = explode(':', $metaData['override_name']);

                if (count($override) != 2) throw new \RuntimeException("View name is not correctly set (Path: $view->path). Example: Demo:viewName");

                $parentModule = Module::where('system_name', $override[0])->where('enabled', true)->first();

                if (is_null($parentModule)) {
                    throw new \RuntimeException("Override Module '{$override[0]}' not found or not installed in the database.");
                }

                $parentView = self::where('name', $override[1])->where('module_id', $parentModule->id)->first();

                if (is_null($parentView)) {
                    throw new \RuntimeException("Override view '{$override[1]}' not found or not stored in the database.");
                }
            }

            $metaData['file_modified_at'] = $fileTime;
            $metaData['file_hash'] = $fileHash;
            $metaData['module_id'] = $module->id;

            if ($content_changed) $metaData['content_changed'] = true;

            if ($parentView){
                $metaData['parent_id'] = $parentView->id;
            }

            if ($parentView && $content_changed) {
                $parentView->content_changed = true;
                $parentView->save();
            }


            $view->fill($metaData);
            $view->file_hash = $fileHash;

            if ($view->base) {
                $resource_path = OverrideViews::generateView($view);
                $resource_path = Str::after($resource_path, resource_path() . DIRECTORY_SEPARATOR);
                $view->resource_path = $resource_path;
            }

            $view->save();

        }

    }

    public static function checkPaths($module): void
    {
        foreach ($module->moduleViews as $view) {
            if (!file_exists($view->path)) {
                unlink(resource_path($view->resource_path));
                static::deleteEmptyParentDirs(resource_path($view->resource_path));
                $view->delete();
            }
        }
    }

    public static function deleteEmptyParentDirs(string $absoluteFilePath, string $stopDir = 'views')
    {
        $dir = dirname($absoluteFilePath);
        $stopPath = resource_path($stopDir); // z. B. resources/views

        while (Str::startsWith($dir, $stopPath) && $dir !== $stopPath) {
            if (
                File::exists($dir) &&
                count(File::allFiles($dir)) === 0 &&
                count(File::directories($dir)) === 0
            ) {
                File::deleteDirectory($dir);
                $dir = dirname($dir);
            } else {
                break;
            }
        }
    }
}
