<?php

namespace Modules\Core\Services\Resources;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Core\Models\Module;

class OverrideViews
{

    public static function run(array $moduleNames): array
    {
        echo "Run:\n";

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

        echo "{$source} → {$destination}\n";

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

    protected static function handleAttributePatch(string $make, \DOMElement $target, \DOMElement $override): void
    {
        BladeViewPatcher::handleAttributePatchStatic($make, $target, $override);
    }

    protected static function cssToXpath(string $selector): string
    {
        return BladeViewPatcher::cssToXpath($selector);
    }

//    public static function run(array $moduleNames): array
//    {
//        echo "Run:\n";
//
//        foreach ($moduleNames as $moduleName) {
//            $module = Module::where('system_name', $moduleName)->where('enabled', true)->first();
//            if (!$module) {
//                \RuntimeException('Module ' . $module . ' ist not found or not installed');
//            }
//
//            foreach ($module->moduleViews as $view) {
//                if (!$view->base && $view->content_changed) {
//                    static::generateView($view->parent);
//                }
//            }
//        }
//
//        return [
//            'type' => 'success',
//            'message' => 'OK',
//        ];
//
//    }
//
//    public static function generateView(object $view): string {
//        echo "View: " . $view->name . "\n";
//        $source = base_path($view->path);
//
//        if (!File::exists($source)) {
//            throw new \RuntimeException("Source file not found: {$source}");
//        }
//
//        if (!Str::contains($view->path, 'Resources/Views/')) {
//            throw new \RuntimeException("Path must contain 'Resources/Views/': {$view->path}");
//        }
//
//        $relative = Str::after($view->path, 'Resources/Views/');
//
//        $normalized = collect(explode(DIRECTORY_SEPARATOR, $relative))
//            ->map(fn($segment) => strtolower($segment))
//            ->implode(DIRECTORY_SEPARATOR);
//
//        $destination = resource_path('views/' . $normalized);
//
//        File::ensureDirectoryExists(dirname($destination));
//
//        $patched = self::applyPatchesRecursively($view);
//
////        dd($patched);
//
//        File::put($destination, $patched);
//
//        echo "{$source} → {$destination}\n";
//
//        return $destination;
//    }
//
//    protected static function applyPatchesRecursively(object $view, ?string $html = null): string
//    {
//        $html = $html ?? File::get(base_path($view->path));
//
//        $html = preg_replace('/^\s*\{\{\-\-.*?\-\-\}\}\s*/s', '', $html);
//
//
//        foreach ($view->children ?? [] as $child) {
//
//            $childHtml = File::get(base_path($child->path));
//            $html = self::applyOverrides($html, $childHtml, $view->path);
//
//            if (!empty($child->children)) {
//                $childChildren = collect($child->children);
//                $html = self::applyPatchesRecursively((object) [
//                    'path' => $child->path,
//                    'children' => $childChildren,
//                ], $html);
//            }
//
//            $child->content_changed = false;
//            if (method_exists($child, 'save')) {
//                $child->save();
//            }
//        }
//
//        if ($view->content_changed ?? false) {
//            $view->content_changed = false;
//            if (method_exists($view, 'save')) {
//                $view->save();
//            }
//        }
//
//        return $html;
//    }
//
//    protected static function applyOverrides(string $baseHtml, string $patchHtml, string $path): string
//    {
//        libxml_use_internal_errors(true);
//
//        $baseDom = new \DOMDocument();
//        $baseDom->loadHTML($baseHtml);
//        $xpath = new \DOMXPath($baseDom);
//
//        // ❌ Remove comments
//        $patchHtml = preg_replace('/\\{\\{\\-\\-.*?<override.*?<\\/override>.*?\\-\\-\\}\\}/s', '', $patchHtml);
//
//        // Extract only <override>-tags
//        preg_match_all('/<override[^>]*>.*?<\\/override>/si', $patchHtml, $matches);
//        $overrideBlocks = implode('', $matches[0] ?? []);
//
//        if (empty($overrideBlocks)) {
//            return $baseHtml;
//        }
//
//        $patchDom = new \DOMDocument();
/*        $patchDom->loadHTML('<?xml encoding="utf-8" ?><root>' . $overrideBlocks . '</root>');*/
//
//        $overrides = $patchDom->getElementsByTagName('override');
//
//        foreach ($overrides as $override) {
//            $find = $override->getAttribute('find');
//            $make = $override->getAttribute('make');
//
//            if (!$find || !$make) {
//                continue;
//            }
//
//            $targetNodes = $xpath->query(self::cssToXpath($find));
//            if ($targetNodes->length === 0) {
//                throw new \RuntimeException(
//                    "[OVERRIDE ERROR] Element with selector \"{$find}\" could not be found in base file: {$path}"
//                );
//            }
//
//            $target = $targetNodes->item(0);
//            $fragment = $baseDom->createDocumentFragment();
//            $content = '';
//
//            foreach ($override->childNodes as $childNode) {
//                $content .= $patchDom->saveHTML($childNode);
//            }
//
//            @$fragment->appendXML($content);
//
//            match ($make) {
//                'before' => $target->parentNode->insertBefore($fragment, $target),
//                'after' => $target->parentNode->insertBefore($fragment, $target->nextSibling),
//                'replace' => $target->parentNode->replaceChild($fragment, $target),
//                default => self::handleAttributePatch($make, $target, $override),
//            };
//        }
//
//        libxml_clear_errors();
//
//        // 🧽 Nur die XML-Zeile entfernen
//        return $baseDom->saveHTML();
//
//    }
//
//    protected static function handleAttributePatch(string $make, \DOMElement $target, \DOMElement $override): void
//    {
//        if (!str_starts_with($make, 'attribute-')) {
//            return;
//        }
//
//        $attribute = substr($make, strlen('attribute-'));
//
//        $add = $override->getAttribute('add');
//        $remove = $override->getAttribute('remove');
//
//        $existing = $target->getAttribute($attribute);
//        $existingParts = preg_split('/\\s+/', $existing, -1, PREG_SPLIT_NO_EMPTY);
//
//        // Entfernen
//        if ($remove) {
//            $toRemove = preg_split('/\\s+/', $remove, -1, PREG_SPLIT_NO_EMPTY);
//            $existingParts = array_diff($existingParts, $toRemove);
//        }
//
//        // Hinzufügen
//        if ($add) {
//            $toAdd = preg_split('/\\s+/', $add, -1, PREG_SPLIT_NO_EMPTY);
//            $existingParts = array_merge($existingParts, $toAdd);
//            $existingParts = array_unique($existingParts);
//        }
//
//        $target->setAttribute($attribute, trim(implode(' ', $existingParts)));
//    }
//
//    protected static function cssToXpath(string $selector): string
//    {
//        static $converter;
//
//        if (!$converter) {
//            $converter = new \Symfony\Component\CssSelector\CssSelectorConverter();
//        }
//
//        return $converter->toXPath($selector);
//    }
}
