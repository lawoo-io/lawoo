<?php

namespace Modules\Core\Services\Schemas;

use http\Exception\RuntimeException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Modules\Core\Models\DbModel;
use Modules\Core\Models\MigrationFile;
use Modules\Core\Models\Module;
use Modules\Core\Models\ModuleUiTranslation;
use Modules\Core\Services\PathService;

class MigrationManager
{

    public static function run($moduleName, $testMigrations = false): array
    {
        $module = Module::where('system_name', $moduleName)->first();

        $dbModels = DbModel::where('changed', true)->whereHas('modules', function($table) use ($module) {
            $table->where('module_id', $module->id);
        })->orderBy('sequence')->get();

        $coreModulePath = PathService::getModulePath('Core');

        $stubPathCreate = $coreModulePath . '/Database/Stubs/migrate_create.stub';
        $stubPathUpdate = $coreModulePath . '/Database/Stubs/migrate_change.stub';

        $outputdir = PathService::getModulePath($moduleName) . '/Database/Migrations';

        $count = 0;
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
            $migrationPath = static::generateMigrationFileFromStub($dbModel->name, $stubPath, $outputdir, $dbModel->dbFields, $tableMode, $count);

            if (!$migrationPath) return ['type' => 'danger', 'message' => 'Migration could not be generated'];

            $path = str_replace(base_path() . '/', '', $migrationPath);

            $migrationFile = MigrationFile::create(['path' => $path, 'db_model_id' => $dbModel->id, 'module_id' => $module->id]);

            static::runMigrateByFilePath($migrationFile, $dbModel);

            if ($testMigrations) {
                return ['type' => 'success', 'message' => 'Migrations have been created.'];
            }

            $dbModel->setMigrateOff();
            $dbModel->dbFields()->each(fn ($field) => $field->setMigrateOff());
            static::removeFields($dbModel->dbFields, $module->id);

            $count++;
        }

        return ['type' => 'success', 'message' => 'Migrations have been created.'];
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
            })->orderBy('sequence', 'desc')->get();

        foreach ($dbModels as $dbModel) {

            // migration reset, delete migration files
            $migrationFiles = $dbModel->migrationFiles->where('module_id', $module->id);
            foreach ($migrationFiles as $migrationFile) {
                echo $migrationFile->path . "\n";
                Artisan::call('migrate:reset', ['--path' => $migrationFile->path, '--force' => true]);
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
            Artisan::call('migrate', [
                '--path'  => $migrationFile->path,
                '--force' => true,
            ]);
            $migrationFile->migrated = true;
            $migrationFile->save();
        } catch (\Exception $e) {
            echo 'Migrate ERROR: ' . $migrationFile->path . "\n";
            echo "Please check LOG\n";
            logger()->error("Error: " . $e->getMessage());
        }
    }

    public static function generateMigrationFileFromStub(string $table, string $stubPath, string $outputDir, object $dbFields, string $tableMode, int $count): string {

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
                    if (Str::startsWith('unique_', $field->name)) {
                        $reverse[] = "\$table->dropUnique('$field->name');";
                    } elseif(Str::startsWith('index_', $field->name)) {
                        $reverse[] = "\$table->dropIndex('$field->name');";
                    } else {
                        $reverse[] = "\$table->dropColumn('$field->name');";
                    }
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
        $fileName = "{$timestamp}_{$count}_{$tableMode}_{$table}_table.php";
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

        if ($type === 'unique') {
            $code = "\$table->unique($param)";
        } elseif($type === 'index') {
            $code = "\$table->index($param)";
        } else {
            $code = "\$table->{$type}('{$name}'" . ($param ? ", {$param}" : "") . ")";
        }


        foreach ($parts as $modifier) {
            if (str_contains($modifier, '=')) {
                [$method, $value] = explode('=', $modifier, 2);
                if ($method === 'query') {
                    $code .= "->{$value}";
                } else {
                    $code .= "->{$method}('{$value}')";
                }
            } else {
                $code .= "->{$modifier}()";
            }
        }

        if ($mode === 'update' && !$type === 'unique' && !$type === 'index') {
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
