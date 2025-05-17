<?php

namespace Modules\Core\Services\Modules;

use Modules\Core\Models\Module;
use Modules\Core\Services\Classes\ClassOverrider;

class ModuleUpdater
{

    public static function run(string $moduleName): array
    {
        $mod = Module::where('system_name', $moduleName)->first();
        if (!$mod) {
            return [
                'type' => 'error',
                'message' => 'Module does not exist.',
            ];
        } elseif (!$mod->enabled) {
            return [
                'type' => 'error',
                'message' => 'Module is not installed.',
            ];
        }

        $path = 'modules' . '/' . $moduleName;

        ClassOverrider::scan($path, $mod->id);

        return [
            'type' => 'success',
            'message' => 'Module updated successfully.',
        ];

    }
}
