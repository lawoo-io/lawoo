<?php

namespace Modules\Core\Repositories;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Core\Abstracts\BaseRepository;
use Modules\Core\Models\Module;
use ReflectionClass;

class ModuleRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Module);
    }

    /**
     * @param $name
     * @return Module|null
     */
    public function getBySystemName($name): Module|null
    {
        return $this->model->where('system_name', $name)->first() ?: null;
    }

    /**
     * Registriert alle aktivierten Module in richtiger Reihenfolge (abhängig von depends)
     */
    public function registerEnabledModules(): void
    {
        if (!Schema::hasTable('modules')) return;

        $modules = $this->model->where('enabled', 1)->get();

        $ordered = [];
        $visited = [];

        foreach ($modules as $module) {
            $dependencies = $this->getEnabledDependencies($module, $visited);

            foreach ($dependencies as $dependency) {
                if (!in_array($dependency->system_name, $ordered)) {
                    $ordered[] = $dependency->system_name;
                }
            }

            if (!in_array($module->system_name, $ordered)) {
                $ordered[] = $module->system_name;
            }
        }

        // Register Module
        foreach ($ordered as $moduleName) {
            $providerClass = "Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider";

            if (class_exists($providerClass)) {
                App::register($providerClass);
            }
        }

    }

    /**
     * Gibt alle aktivierten Abhängigkeiten eines Moduls zurück (rekursiv, topologisch sortiert)
     */
    public function getEnabledDependencies(Module $module, array &$visited = []): array
    {
        $result = [];

        foreach ($module->dependencies as $dependency) {
            if ($dependency->enabled && !in_array($dependency->id, $visited)) {
                $visited[] = $dependency->id;

                $subDeps = $this->getEnabledDependencies($dependency, $visited);

                $result = array_merge($result, $subDeps);
                $result[] = $dependency;
            }
        }

        return $result;
    }

    public function registerLivewireForAll(): void
    {
        if (!Schema::hasTable('modules')) return;


        $modules = $this->model->where('enabled', 1)->get();

        $ordered = [];
        $visited = [];

        foreach ($modules as $module) {
            $dependencies = $this->getEnabledDependencies($module, $visited);

            foreach ($dependencies as $dependency) {
                if (!in_array($dependency->system_name, $ordered)) {
                    $ordered[] = $dependency->system_name;
                }
            }

            if (!in_array($module->system_name, $ordered)) {
                $ordered[] = $module->system_name;
            }
        }

        foreach ($ordered as $moduleName) {

            $this->registerLivewireComponents($moduleName);
        }
    }

    /**
     * Register Livewire Components
     */
    public function registerLivewireComponents(string $moduleName): void
    {
        $basePath = base_path("modules/{$moduleName}/Http/Livewire");

        if (!is_dir($basePath)) return;

        $files = collect(File::allFiles($basePath))
            ->filter(fn ($file) => Str::endsWith($file->getFilename(), '.php'));

        foreach ($files as $file) {
            $relativePath = Str::after($file->getPathname(), base_path() . '/');
            $class = str_replace(['/', '.php'], ['\\', ''], $relativePath);
            $class = Str::replaceFirst('modules\\', 'Modules\\', $class);

            if (!class_exists($class)) continue;

            $reflection = new ReflectionClass($class);
            if (! $reflection->isSubclassOf(\Livewire\Component::class)) continue;

//            $componentName = Str::of($class)
//                ->after("Modules\\")
//                ->replace(['\\Http\\Livewire\\', '\\'], ['.', '.'])
//                ->replace('Component', '')
//                ->lower();
            $componentName = Str::of($class)
                ->after("Modules\\")
                ->replace(['\\Http\\Livewire\\', '\\'], ['.', '.'])
                ->replace('Component', '')
                ->pipe(function($str) {
                    // Teile durch Punkte trennen, jeden Teil kebab-case, wieder zusammenfügen
                    $parts = explode('.', $str);
                    return implode('.', array_map(fn($part) => Str::kebab($part), $parts));
                });

            Livewire::component($componentName->toString(), $class);
        }
    }
}
