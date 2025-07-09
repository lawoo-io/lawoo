<?php

namespace Modules\Core\Services\Resources;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Core\Models\ModuleView;

class ResourceBuild
{

    public static function run (array|string $moduleNames = '*'): array
    {
        $originalCwd = getcwd();
        chdir(base_path());

        $modules = (array) $moduleNames;

        $count = 0;

        foreach ($modules as $module) {

            $resourcePath = config('app.modules_base_path') . "/{$module}/Resources";

            if (!File::isDirectory($resourcePath)) {
                echo "📁 Resources directory not found for module: {$module}";
                continue;
            }

            $phpFiles = File::allFiles($resourcePath);

            foreach ($phpFiles as $file) {

                if ($file->getExtension() !== 'php') {
                    self::copyAssets($file, $module);
                    continue;
                }

                $contents = File::get($file->getPathname());

                // Sucht ein Kommentare im oberen Bereich der Datei
                if (preg_match('/^\s*\{\{\-\-(.*?)\-\-\}/s', $contents, $match)) {
                    $raw = trim($match[1]);

                    // Metadaten parser
                    $parsed = static::parseMetadataBlock($raw);

                    if ($parsed) {
                        $path = $file->getPathname();
                        $parsed['path'] = Str::after($path, base_path() . DIRECTORY_SEPARATOR);

                        $fileTime = Carbon::createFromTimestamp(filemtime($path));
                        $fileHash = sha1_file($path);

                        ModuleView::saveOrUpdateMetaData($parsed, $fileTime, $fileHash, $module);

                        $count++;
                    }
                }
            }
        }

        chdir($originalCwd);

        return [
            'type' => 'success',
            'message' => $count . ' files successfully stored.',
        ];
    }

    protected static function copyAssets(object $file, string $moduleName): void
    {
        $moduleNameSlug = strtolower($moduleName);
        $fullPath = $file->getPathname();

        $moduleBasePath = config('app.modules_base_path') . '/' . $moduleName . '/Resources';
        $relativePath = Str::after($fullPath, $moduleBasePath);

        $pathParts = explode(DIRECTORY_SEPARATOR, $relativePath);
        $typeFolder = strtolower(array_shift($pathParts));

        $targetPath = resource_path("{$typeFolder}/{$moduleNameSlug}/" . implode('/', $pathParts));


        File::ensureDirectoryExists(dirname($targetPath));
        File::copy($fullPath, $targetPath);
    }

    protected static function parseMetadataBlock(string $block): ?array
    {
        $pattern = "/(\w+)\s*:\s*'([^']*)'|\b(\w+)\s*:\s*(\d+)/";
        preg_match_all($pattern, $block, $matches, PREG_SET_ORDER);

        $result = [];

        foreach ($matches as $match) {
            if (!empty($match[1]) && isset($match[2])) {
                $result[$match[1]] = $match[2];
            } elseif (!empty($match[3]) && isset($match[4])) {
                $result[$match[3]] = (int) $match[4];
            }
        }

        return $result ?: null;
    }
}
