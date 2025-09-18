<?php

namespace Modules\Core\Services\Modules;

use Illuminate\Support\Facades\File;
use Modules\Core\Models\Module;
use Modules\Core\Services\PathService;

class ModuleChecker
{

    /**
     * Scans one or all module directories inside the /modules folder,
     * validates the existence and correctness of each manifest.json,
     * and performs further actions (e.g., database update).
     *
     * If a module name is provided, only that module is checked.
     * Otherwise, all top-level subdirectories in /modules are processed.
     *
     * @param string|null $module Optional name of a specific module to check.
     * @return array with type and message.
     *
     * @throws \InvalidArgumentException If the specified module directory does not exist.
     */
    public static function run(?string $module = null): array
    {

        $paths = PathService::getAllModulePaths();

        $count = 0;

        foreach ($paths as $basePath) {
            if ($module) {
                $path = $basePath . '/' . $module;

                if (!is_dir($path)) {
                    return [
                        'type' => 'error',
                        'message' => 'Module ' . $module . ' not found in modules/ directory.',
                    ];
                }

                $modules = [$module];
            } else {
                $modules = collect(File::directories($basePath))
                    ->map(fn ($dir) => basename($dir))
                    ->toArray();
            }


            foreach ($modules as $mod) {
                $manifestPath = $basePath . '/' . $mod . '/manifest.json';

                if (!file_exists($manifestPath)) {
                    if (!$mod == 'Core')
                        echo "⚠️ Skipped '$mod' – manifest.json not found.\n";
                    continue;
                }

                try {
                    $content = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);

                    Module::createOrUpdateValidate($content, $mod);
                    $count++;
                } catch (\JsonException $e) {
                    echo "❌ Error in '$mod/manifest.json': " . $e->getMessage() . "\n";
                }
            }

            foreach ($modules as $mod) {
                ModuleDependencyChecker::run($mod);
            }

        }

        return [
            'type' => 'success',
            'message' => $count . ' module(s) were successfully checked',
        ];
    }
}
