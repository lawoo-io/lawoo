<?php

namespace Modules\Core\Services\Schemas;

use Illuminate\Support\Facades\Artisan;
use Modules\Core\Models\DbField;
use Modules\Core\Models\DbModel;
use Modules\Core\Models\MigrationFile;
use Modules\Core\Models\Module;
use Modules\Core\Models\ModuleUiTranslation;
use Modules\Core\Models\YamlFile;
use Modules\Core\Services\TranslationImporter;

class MigrationManager
{

    public static function run($moduleName): void
    {
        $module = Module::where('system_name', $moduleName)->first();

        $dbModels = DbModel::where('changed', true)->whereHas('modules', function($table) use ($module) {
            $table->where('module_id', $module->id);
        })->get();

        $stubPathCreate = base_path() . '/modules/Core/Database/Stubs/migrate_create.stub';
        $stubPathUpdate = base_path() . '/modules/Core/Database/Stubs/migrate_change.stub';

        $outputdir = base_path() . "/modules/$moduleName/Database/Migrations";

        foreach ($dbModels as $dbModel) {

            if ($dbModel->new) {
                $stubPath = $stubPathCreate;
                $tableMode = 'create';
            } else {
                $stubPath = $stubPathUpdate;
                $tableMode = 'update';
            }

            // delete old not migrated migration files
            MigrationFile::deleteBeforeCreate($dbModel->id);

            // create new migration file
            $migrationPath = static::generateMigrationFileFromStub($dbModel->name, $stubPath, $outputdir, $dbModel->dbFields, $tableMode);

            if (!$migrationPath) return;

            $path = str_replace(base_path() . '/', '', $migrationPath);

            $migrationFile = MigrationFile::create(['path' => $path, 'db_model_id' => $dbModel->id, 'module_id' => $module->id]);

            static::runMigrateByFilePath($migrationFile, $dbModel);

            $dbModel->setMigrateOff();
            $dbModel->dbFields()->each(fn ($field) => $field->setMigrateOff());
            static::removeFields($dbModel->dbFields, $module->id);        }
    }


    public static function removeFields($dbFields, int $moduleId): void
    {
        foreach ($dbFields as $dbField) {
            if ($dbField->to_remove && $dbField->module_id === $moduleId) {
                $dbField->delete();
            }
        }
    }

    public static function removeDb(string $moduleName): void
    {
        $module = Module::where('system_name', $moduleName)->first();
        $dbModels = DbModel::with(['migrationFiles', 'dbFields', 'yamlFiles'])
            ->whereHas('modules', function($table) use ($module) {
                $table->where('module_id', $module->id);
            })->get();

        foreach ($dbModels as $dbModel) {

            // migration reset, delete migration files
            foreach ($dbModel->migrationFiles->where('module_id', $module->id) as $migrationFile) {
                Artisan::call('migrate:reset', ['--path' => $migrationFile->path]);
                $migrationFile->delete();
            }

            // delete fields
            foreach ($dbModel->dbFields->where('module_id', $module->id) as $dbField) {
                $dbField->delete();
            }

            // delete YamlFiles
            foreach ($dbModel->yamlFiles->where('module_id', $module->id) as $yamlFile) {
                $yamlFile->delete();
            }

            $dbModel->load(['migrationFiles', 'dbFields', 'yamlFiles']);
            if(!$dbModel->migrationFiles->count() && !$dbModel->dbFields->count() && !$dbModel->yamlFiles->count()) {
                $dbModel->delete();
            }
        }

        // Remove Translations
        ModuleUiTranslation::where('module', $moduleName)->delete();

        /**
         * Delete Module Translations
         */
//        $importer = app(TranslationImporter::class);
//        $importer->deleteModuleTranslations($moduleName);

    }

    public static function runMigrateByFilePath(MigrationFile $migrationFile, DbModel $dbModel): void
    {
        try {
            Artisan::call("migrate --path=$migrationFile->path");
            $migrationFile->migrated = true;
            $migrationFile->save();
        } catch (\Exception $e) {
            logger()->error("Error: " . $e->getMessage());
        }
    }

    public static function generateMigrationFileFromStub(string $table, string $stubPath, string $outputDir, object $dbFields, string $tableMode): string {

        $stub = file_get_contents($stubPath);
        $fields = [];
        $reverse = [];

        foreach ($dbFields as $field) {

            if (!$field->changed && !$field->to_remove) continue;

            $raw = $field->new_param ? $field->new_params : $field->params;

            if ($field->to_remove && $tableMode === 'update') {
                $fields[] = "\$table->dropColumn('$field->name');";

                $reverse[] = static::generateColumnLine($field->name, $raw, 'create');
            } else {
                $mode = $field->created ? 'update' : 'create';
                $fields[] = static::generateColumnLine($field->name, $raw, $mode);

                if ($tableMode === 'update') {
                    $reverse[] = "\$table->dropColumn('$field->name');";
                }
            }
        }

        if (!count($fields)) return false;

        $filled = str_replace(
            ['{{action}}', '{{reverse_action}}', '{{table}}', '{{fields}}', '{{reverse_fields}}'],
            [
                $tableMode === 'create' ? 'create' : 'table',
                $tableMode === 'create' ? 'dropIfExists' : 'table',
                $table,
                implode(PHP_EOL . '            ', $fields),
                implode(PHP_EOL . '            ', $reverse)
            ],
            $stub
        );

        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$tableMode}_{$table}_table.php";
        $path = rtrim($outputDir, '/') . '/' . $fileName;

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        file_put_contents($path, $filled);

        return $path;
    }


    public static function generateColumnLine(string $name, string $raw, string $mode = 'create'): string
    {
        $parts = explode('.', $raw);
        $typePart = array_shift($parts);

        $typeSplit = explode('=', $typePart);
        $type = $typeSplit[0];
        $param = $typeSplit[1] ?? null;

        $code = "\$table->{$type}('{$name}'" . ($param ? ", {$param}" : "") . ")";

        foreach ($parts as $modifier) {
            if (str_contains($modifier, '=')) {
                [$method, $value] = explode('=', $modifier, 2);
                $code .= "->{$method}('{$value}')";
            } else {
                $code .= "->{$modifier}()";
            }
        }

        if ($mode === 'update') {
            $code .= "->change()";
        }

        return $code . ';';
    }


    public static function prepareFields(object $fields, bool $create = true): array
    {
        $result = [];
        foreach ($fields as $field) {
            $result[$field->name] = $create === true ? $field->params : $field->new_params;
        }

        return $result;
    }
}
