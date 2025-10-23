<?php

namespace Modules\Core\Services\Resources;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Core\Models\Module;

class OverrideViews
{

    public static function run(array $moduleNames): array
    {

        foreach ($moduleNames as $moduleName) {
            $module = Module::where('system_name', $moduleName)->where('enabled', true)->first();
            if (!$module) {
                throw new \RuntimeException('Module ' . $moduleName . ' is not found or not installed');
            }

            foreach ($module->moduleViews as $view) {
                if (!$view->base && $view->content_changed) {
                    static::generateView($view->parent);
                }
            }
        }

        return [
            'type' => 'success',
            'message' => 'OK',
        ];
    }

    public static function generateView(object $view): string
    {
        $source = base_path($view->path);

        if (!File::exists($source)) {
            throw new \RuntimeException("Source file not found: {$source}");
        }

        if (!Str::contains($view->path, 'Resources/Views/')) {
            throw new \RuntimeException("Path must contain 'Resources/Views/': {$view->path}");
        }

        $relative = Str::after($view->path, 'Resources/Views/');
        $normalized = collect(explode(DIRECTORY_SEPARATOR, $relative))
            ->map(fn($segment) => strtolower($segment))
            ->implode(DIRECTORY_SEPARATOR);

        $destination = resource_path('views/' . $normalized);

        File::ensureDirectoryExists(dirname($destination));

        $patched = self::applyPatchesRecursively($view);
        File::put($destination, $patched);

        Log::info("{$source} → {$destination}");

        return $destination;
    }

    protected static function applyPatchesRecursively(object $view, ?string $html = null): string
    {
        $html = $html ?? File::get(base_path($view->path));
        $html = preg_replace('/^\s*\{\{\-\-.*?\-\-\}\}\s*/s', '', $html);

        foreach ($view->children ?? [] as $child) {
            $childHtml = File::get(base_path($child->path));
            $html = self::applyOverrides($html, $childHtml, $view->path);

            if (!empty($child->children)) {
                $childChildren = collect($child->children);
                $html = self::applyPatchesRecursively((object) [
                    'path' => $child->path,
                    'children' => $childChildren,
                ], $html);
            }

            $child->content_changed = false;
            if (method_exists($child, 'save')) {
                $child->save();
            }
        }

        if ($view->content_changed ?? false) {
            $view->content_changed = false;
            if (method_exists($view, 'save')) {
                $view->save();
            }
        }

        return $html;
    }

    protected static function applyOverrides(string $baseHtml, string $patchHtml, string $path): string
    {
        return BladeViewPatcher::fromHtml($baseHtml, $path)
            ->patchWith($patchHtml)
            ->render();
    }

//    protected static function handleAttributePatch(string $make, \DOMElement $target, \DOMElement $override): void
//    {
//        BladeViewPatcher::handleAttributePatchStatic($make, $target, $override);
//    }
//
//    protected static function cssToXpath(string $selector): string
//    {
//        return BladeViewPatcher::cssToXpath($selector);
//    }

}
