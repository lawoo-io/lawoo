<?php

namespace Modules\Core\Services\Schemas;

use Modules\Core\Models\DbField;
use Modules\Core\Models\DbModel;
use Modules\Core\Models\Module;
use Symfony\Component\Yaml\Yaml;

class DbFieldManager
{
    public static function run($moduleName): void
    {
        $module = Module::where('system_name', $moduleName)->first();

        $dbModels = DbModel::where('changed', true)->whereHas('modules', function($table) use ($module) {
            $table->where('module_id', $module->id);
        })->get();

        foreach ($dbModels as $dbModel) {
            $yamlFiles = $dbModel->yamlFiles()->orderBy('file_modified_at', 'asc')->get();

            $models = [];

            foreach ($yamlFiles as $yamlFile) {
                $parsed = Yaml::parseFile($yamlFile->path);

                foreach ($parsed as $table => $data) {
                    $models[$table] = $data['fields'];
                    foreach ($data['fields'] as $field => $params) {
                        $models[$table][$field] = $params;
                    }
                }
            }

            // create or update fields
            foreach ($models as $key => $fields) {
                if ($key === $dbModel->name) {
                    DbField::createOrUpdate($dbModel->id, $fields, $dbModel->name, $module->id);
                }
            }

            // set fields to remove
            foreach ($models as $key => $fields) {
                if ($key === $dbModel->name) {
                    $dbFields = DbField::where('module_id', $module->id)->get();
                    DbField::setToRemove($dbFields, $fields);
                }
            }
        }
    }
}
