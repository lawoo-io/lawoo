<?php

namespace Modules\Core\Services\Modules;

use Modules\Core\Models\Module;
use Modules\Core\Services\Classes\ClassOverrider;

class ModuleInstaller
{

    public static function run(string $moduleName): array
    {
        $mod = Module::where('system_name', $moduleName)->first();
        if (!$mod) {
            return [
                'type' => 'error',
                'message' => 'Module does not exist.',
            ];
        } elseif ($mod->enabled) {
            return [
                'type' => 'error',
                'message' => 'Module is already installed. Please use: modules:update ' . $moduleName . '.',
            ];
        }

        $path = 'modules' . '/' . $moduleName;

        ClassOverrider::scan($path, $mod->id);

        $mod->enabled = true;
        $mod->version_installed = $mod->version;
        $mod->save();

        return [
            'type' => 'success',
            'message' => "Module $moduleName installed successfully.",
        ];
    }
}
