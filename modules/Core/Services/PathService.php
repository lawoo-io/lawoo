<?php

namespace Modules\Core\Services;

class PathService
{
    public static function getByModule($module): string
    {
        $path = '';
        foreach (config('app.modules_base_paths') as $base_path) {
            if(is_dir($base_path.'/'.$module)) {
                $path = $base_path;
                break;
            }
        }

        if(empty($path)) {
            throw new \RuntimeException('Module path not found');
        }

        return $path;
    }

    public static function getModulePath($module): string
    {
        return PathService::getByModule($module) . '/' . $module;
    }

    public static function getAllModulePaths(): array
    {
        return config('app.modules_base_paths') ?? [];
    }
}
