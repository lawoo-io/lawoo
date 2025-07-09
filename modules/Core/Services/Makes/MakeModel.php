<?php

namespace Modules\Core\Services\Makes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Core\Services\PathService;

class MakeModel
{

    public function __construct(){}

    /**
     * Create a Model and a Schema file
     * @param string $name
     * @param string $module
     * @param bool $schema
     * @return string[]
     */
    public static function run(string $name, string $module, bool $schema = false): array
    {
        $modulePath = PathService::getModulePath($module);
        $modelPath = $modulePath . "/Models/{$name}.php";
        $schemaPath = $modelPath . "/Database/Schemas/" . Str::kebab($name) . ".yml";

        $coreModulePath = PathService::getModulePath('Core');
        $stubDir = $coreModulePath . "/Console/Stubs";
        $modelStubPath = "{$stubDir}/model.stub";
        $schemaStubPath = "{$stubDir}/schema.stub";

        $messages = '';
        $type = 'success';

        // === Create Model File ===
        if (!File::exists($modelPath)) {
            $tableName = Str::snake(Str::plural($name));
            $modelStub = file_get_contents($modelStubPath);

            $filledModel = str_replace(
                ['{{name}}', '{{module}}', '{{table}}'],
                [$name, $module, $tableName],
                $modelStub
            );

            if (!is_dir(dirname($modelPath))) {
                mkdir(dirname($modelPath), 0755, true);
            }

            file_put_contents($modelPath, $filledModel);
            $messages = "✅ Model created: {$modelPath}";
        } else {
            $messages = "⚠️ Model already exists: {$modelPath}";
            $type = 'info';
        }

        // === Create Schema (optional) ===
        if ($schema) {
            if (!File::exists($schemaPath)) {
                $tableName = Str::snake(Str::plural($name));
                $schemaStub = file_get_contents($schemaStubPath);

                $filledSchema = str_replace(['{{table}}'], [$tableName], $schemaStub);

                if (!is_dir(dirname($schemaPath))) {
                    mkdir(dirname($schemaPath), 0755, true);
                }

                file_put_contents($schemaPath, $filledSchema);
                $messages .= "\n✅ Schema created: {$schemaPath}";
            } else {
                $messages .= "\n⚠️ Schema already exists: {$schemaPath}";
                $type = 'info';
            }
        }

        return [
            'type' => $type,
            'messages' => $messages
        ];
    }
}
