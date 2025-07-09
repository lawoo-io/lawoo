<?php

namespace Modules\Core\Services\Makes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Core\Services\PathService;

class MakeLivewire
{
    public static function run(string $name, string $module, bool $res = false): array
    {
        $modulePath = PathService::getModulePath($module);
        $componentPath = $modulePath . "/Http/Livewire/{$name}.php";

        if ($res) {
            $viewPath = $modulePath . "/Resources/Views/livewire/"
                . Str::kebab($module) . "/" . Str::kebab($name) . ".blade.php";
        }

        if (str_contains($name, 'List')){
            $stubName = 'livewire-list.stub';
        } elseif (str_contains($name, 'Form')){
            $stubName = 'livewire-form.stub';
        } elseif (str_contains($name, 'Kanban')){
            $stubName = 'livewire-kanban.stub';
        } else {
            $stubName = 'livewire.stub';
        }

        $coreModulePath = PathService::getModulePath('Core');

        $stubDir = $coreModulePath . "/Console/Stubs";
        $componentStubPath = "{$stubDir}/{$stubName}";
        $viewStubPath = "{$stubDir}/view.stub";

        $messages = '';
        $type = 'success';


        // === Create Livewire Component File ===
        if (!File::exists($componentPath)) {
            $view = 'livewire:' . Str::snake($module) . '.' . Str::snake($name);
            $livewireStub = file_get_contents($componentStubPath);

            $filledLivewire = str_replace(
                ['{{name}}', '{{module}}', '{{view}}'],
                [$name, $module, $view],
                $livewireStub
            );

            if (!is_dir(dirname($componentPath))) {
                mkdir(dirname($componentPath), 0755, true);
            }

            file_put_contents($componentPath, $filledLivewire);
            $messages = "✅ Livewire Component created: {$componentPath}";
        } else {
            $messages = "⚠️ Livewire Component already exists: {$componentPath}";
            $type = 'info';
        }


        // === Create View (optional) ===
        if ($res) {
            if (!File::exists($viewPath)) {
                $viewName = 'livewire_' . Str::snake($name);
                $viewStub = file_get_contents($viewStubPath);

                $filledView = str_replace(['{{view}}'], [$viewName], $viewStub);

                if (!is_dir(dirname($viewPath))) {
                    mkdir(dirname($viewPath), 0755, true);
                }

                file_put_contents($viewPath, $filledView);
                $messages .= "\n✅ View created: {$viewPath}";
            } else {
                $messages .= "\n⚠️ View already exists: {$viewPath}";
                $type = 'info';
            }
        }

        return [
            'type' => $type,
            'messages' => $messages
        ];
    }

}
