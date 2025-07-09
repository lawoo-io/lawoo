<?php

namespace Modules\Core\Services\Classes;

use Illuminate\Support\Facades\File;
use Modules\Core\Models\Override;
use Modules\Core\Repositories\ModuleRepository;
use Modules\Core\Repositories\OverrideRepository;

class ClassOverrider
{

    public static function scan(string $path, object $module): void
    {

        $overrideRepository = app(OverrideRepository::class);
        $overrideRepository->check($module);

        $overrides = [];

        // Get all files recursively from the given base path
        $phpFiles = File::allFiles($path);

        foreach ($phpFiles as $file) {
            // Only process PHP files
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $realPath = $file->getRealPath();
            $code = file_get_contents($realPath);

            // Skip files that do not mention OVERRIDE_TARGET
            if (!str_contains($code, 'OVERRIDE_TARGET')) {
                continue;
            }

            // Extract namespace and class name
            if (preg_match('/namespace\s+(.+);/', $code, $nsMatch) &&
                preg_match('/class\s+(\w+)/', $code, $classMatch)) {

                $namespace = trim($nsMatch[1]);
                $className = trim($classMatch[1]);
                $fqcn = $namespace . '\\' . $className;

                // Make sure the class is loaded to access its constant
                if (!class_exists($fqcn)) {
                    require_once $realPath;
                }

                // Skip if the class does not define OVERRIDE_TARGET
                if (!defined("$fqcn::OVERRIDE_TARGET")) {
                    continue;
                }

                // Get the target class that is being overridden
                $target = constant("$fqcn::OVERRIDE_TARGET");

                Override::updateOrCreate(
                    [
                        'original_class' => $target,
                        'override_class' => $fqcn
                    ],
                    [
                        'module_id' => $module->id
                    ]
                );
            }
        }
    }
}
