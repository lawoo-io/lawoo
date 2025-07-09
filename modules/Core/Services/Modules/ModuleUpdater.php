<?php

namespace Modules\Core\Services\Modules;

use Modules\Core\Models\Module;
use Modules\Core\Repositories\ModuleRepository;
use Modules\Core\Services\Classes\ClassOverrider;

class ModuleUpdater
{

    public static function run(string $moduleName): array
    {
//        $mod = Module::where('system_name', $moduleName)->first();

        $moduleRepository = app(ModuleRepository::class);
        $module = $moduleRepository->getBySystemName($moduleName);

        if (!$module) {
            return [
                'type' => 'error',
                'message' => 'Module does not exist.',
            ];
        } elseif (!$module->enabled) {
            return [
                'type' => 'error',
                'message' => 'Module is not installed.',
            ];
        }

        $path = config('app.modules_base_path') . '/' . $moduleName;

        ClassOverrider::scan($path, $module);

        return [
            'type' => 'success',
            'message' => 'Module updated successfully.',
        ];

    }
}
