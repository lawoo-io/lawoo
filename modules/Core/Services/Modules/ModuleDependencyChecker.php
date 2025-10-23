<?php

namespace Modules\Core\Services\Modules;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\Module;
use Modules\Core\Services\PathService;

class ModuleDependencyChecker
{

    public static function run(string $module = null): void
    {
        $basePath = PathService::getByModule($module);

        if ($module) {
            $modules = [$module];
        } else {
            $modules = collect(File::directories($basePath))
                ->map(fn ($dir) => basename($dir))
                ->toArray();
        }

        foreach ($modules as $mod) {
            $manifestPath = $basePath . '/' . $mod . '/manifest.json';

            if (!file_exists($manifestPath)) {
                continue;
            }

            try {
                $content = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
                Module::attachDependency($content, $mod);
            } catch (\JsonException $e) {
                $message = "Error in '$mod/manifest.json': " . $e->getMessage();

                if (app()->runningInConsole()) {
                    echo $message . PHP_EOL;
                } else {
                    Log::error($message);
                    throw $e;
                }
            }
        }
    }

}
