<?php

namespace Modules\Core\Services\Modules;

use Modules\Core\Models\Module;

class ModuleRemover
{
    public static function run(string $moduleName): array
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

        return [
            'type' => 'success',
            'message' => 'Module ' . $moduleName . ' was successfully removed.'
        ];

    }


}
