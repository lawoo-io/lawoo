<?php

namespace Modules\Core\Services\Modules;

use Modules\Core\Models\Module;
use Modules\Core\Services\Classes\ClassOverrider;
use Modules\Core\Services\PathService;

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
                'message' => 'Module is already installed. Please use: lawoo:update ' . $moduleName . '.',
            ];
        }

        $path = PathService::getModulePath($moduleName) ;

        ClassOverrider::scan($path, $mod);

        $mod->enabled = true;
        $mod->version_installed = $mod->version;
        $mod->save();

        return [
            'type' => 'success',
            'message' => "Module $moduleName installed successfully.",
        ];
    }
}
