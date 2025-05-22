<?php

namespace Modules\Core\Services\Modules;

use Modules\Core\Models\Module;
use Modules\Core\Services\Resources\OverrideViews;
use Modules\Core\Services\Resources\ResourceBuild;

class ModuleRemover
{
    public static function run(string $moduleName, bool $self = false): array
    {
        $module = Module::where('system_name', $moduleName)->first();
        if (!$module) {
            return [
                'type' => 'error',
                'message' => 'Module ' . $moduleName . ' not found.'
            ];
        } elseif (!$module->enabled) {
            return [
                'type' => 'error',
                'message' => 'Module ' . $moduleName . ' is disabled.'
            ];
        }

        /**
         * Delete all overrides classes from the module
         */
        $module->overrides()->delete();

        /**
         * Delete all modelViews entries
         */
        $module->moduleViews()->delete();

        /**
         * Remove version and deactivate the module
         */
        $module->version_installed = '';
        $module->enabled = false;
        $module->save();

        $moduleNames = [];

        foreach ($module->dependencies as $dependency) {
            $moduleNames[] = $dependency->system_name;
        }

        print_r($moduleNames);

        if (count($moduleNames) > 0) {
            foreach ($moduleNames as $moduleName) {
                ResourceBuild::run([$moduleName]);
                OverrideViews::run([$moduleName]);
            }
        }

        return [
            'type' => 'success',
            'message' => 'Module ' . $moduleName . ' was successfully removed.'
        ];

    }


}
