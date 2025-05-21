<?php

namespace Modules\Core\Services\Makes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Core\Repositories\ModuleRepository;
use Modules\Core\Repositories\ModuleViewRepository;

class MakeView
{

    public static function run($name, $moduleName, $component = false): array
    {
        $viewName = Str::snake($name);

        // get Module by name
        $module = app(ModuleRepository::class)->getBySystemName($moduleName);

        // if view name exists
        if (app(ModuleViewRepository::class)->isExists($viewName, $module->id)) {
            throw new \RuntimeException("View '$viewName' already exists in the module '$module->system_name'.");
        }

        if ($component) {
            $viewPath = base_path("modules/{$moduleName}/Resources/Views/components/" .
                Str::kebab($moduleName) . "/" . Str::kebab($name) . ".blade.php");
        } else {
            $viewPath = base_path("modules/{$moduleName}/Resources/Views/modules/" .
                Str::kebab($moduleName) . "/" . Str::kebab($name) . ".blade.php");
        }

        $stubDir = base_path('modules/Core/Console/Stubs');

        $viewStubPath = "{$stubDir}/view.stub";

        $messages = '';
        $type = 'success';

        // === Create View (optional) ===
        if (!File::exists($viewPath)) {
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

        return [
            'type' => $type,
            'messages' => $messages
        ];

    }
}
