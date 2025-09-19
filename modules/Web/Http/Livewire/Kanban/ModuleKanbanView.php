<?php

namespace Modules\Web\Http\Livewire\Kanban;

use Flux\Flux;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\Models\Module;
use Modules\Core\Models\ModuleCategory;
use Modules\Web\Repositories\ModuleRepository;

class ModuleKanbanView extends BaseKanbanView
{
    /**
    * !Required
    * @var string
    */
    public ?string $moduleName = 'Web';

    /**
     * !Required
     * @var string
     */
    public ?string $modelClass = 'Module';

    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = ModuleRepository::class;

    /**
     * @var bool
     */
    public bool $modal = true;

    /**
     * @var array
     */
    public array $confirmData = [];

    public function boot(): void
    {
        parent::boot();
        $this->title = __t('Modules', 'Web');
    }

    public function getAvailableOptions(): array
    {
        return [];
    }

    public function getAvailableButtons(): array
    {
        return [
            'install' => [
                'label' => __t('Install', 'Web'),
                'filter' => [
                    'field' => 'enabled',
                    'value' => false,
                ],
                'variant' => 'primary',
                'click' => 'install',
                'size' => 'xs',
            ],
            'update' => [
                'label' => __t('Update', 'Web'),
                'filter' => [
                    'field' => 'enabled',
                    'value' => true,
                ],
                'click' => 'update',
                'size' => 'xs',
            ],
            'info' => [
                'label' => __t('Info', 'Web'),
                'variant' => 'outline',
                'click' => 'openModal',
                'size' => 'xs',
            ]
        ];
    }

    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'visible' => false,
            ],
            'name' => [
                'visible' => true,
                'type' => 'heading',
                'lever' => 2,
                'class' => 'col-span-4'
            ],
            'short_desc' => [
                'visible' => true,
                'type' => 'text',
                'class' => 'col-span-4 text-xs',
            ],
            'enabled' => [
                'visible' => false,
            ]
        ];
    }

    public static function setSearchFields(): array
    {
        return [
            'name' => __t('Name', 'Web'),
            'system_name' => __t('System Name', 'Web'),
        ];
    }

    public static function setAvailableFilters(): array
    {
        return [
            'filter' => [
                'label' => __t('Filter', 'Web'),
                'column' => 1,
                'filters' => [
                    'enabled' => [
                        'label' => __t('Installed', 'Web'),
                        'type' => 'boolean',
                        'value' => false,
                    ]
                ]
            ],
            'dropdowns' => [
                'label' => __t('Category', 'Web'),
                'column' => 2,
                'filters' => [
                    'module_category_id' => [
                        'label' => __t('Category', 'Web'),
                        'type' => 'relation',
                        'relation' => [
                            'model' => ModuleCategory::class,
                            'key_column' => 'id',
                            'display_column' => 'name',
                        ],
                        'multiple' => true,
                        'operator' => 'whereIn',
                    ]
                ]
            ]
        ];
    }

    public function install(int $id): void
    {
        $module = $this->resolveRepository()->find($id);

        $modules = Module::getDependenciesForInstall($module->system_name);
        $names = array_map(fn($m) => $m->system_name, $modules);

        if (count($names) > 0) {
            $this->confirmData = [
                'heading' => __t('Required Modules Missing', 'Web'),
                'text' => __t('Additional required modules will be installed automatically:', 'Web') . " <strong>" . implode(', ', $names) . "</strong>",
                'button' => [
                    'click' => "installModule({$module->id})",
                    'variant' => 'primary',
                    'label' => __t('Install', 'Web'),
                ]
            ];

            Flux::modal('modal-confirm')->show();
        } else {
            $this->installModule($module->id);
        }

    }

    public function installModule(int $id): void
    {
        try {
            $module = $this->resolveRepository()->find($id);
            Artisan::call('lawoo:install', [
                'module' => $module->system_name,
            ]);
            $this->reset(['confirmData']);
            Flux::modals()->close();
            $this->refresh();
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }

    public function update(int $id): void
    {
        try {
            $module = $this->resolveRepository()->find($id);
            if($module) {
                Artisan::call('lawoo:update', [
                    'module' => $module->system_name,
                ]);
                \Log::info("Module {$module->system_name} updated successfully.");
                $this->refresh();
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }

    public function remove(int $id): void
    {
        $module = $this->resolveRepository()->find($id);
        $modules = Module::getRequiredByDependents($module->system_name);
        $modules = array_merge($modules, [$module->system_name]);

        $this->confirmData = [
            'heading' => __t('Do you want to remove this module?', 'Web'),
            'text' => '<p>' . __t("Following modules will be removed:", "web") . " <strong>{$this->getModuleList($modules)}</strong></p>",
            'button' => [
                'click' => "removeModule({$module->id})",
                'variant' => 'danger',
                'label' => __t('Remove', 'Web')
            ]
        ];

        Flux::modal('modal-confirm')->show();
    }

    public function removeModule(int $id): void
    {
        try {
            $module = $this->resolveRepository()->find($id);
            Artisan::call('lawoo:remove', [
                'name' => $module->system_name,
            ]);
            \Log::info("Module {$module->system_name} removed successfully.");
            Flux::modals()->close();
            $this->reset(['confirmData']);
            $this->js('window.location.reload();');
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }

    protected function getModuleList($modules): string
    {
        $result = '';$i=0;$breaker = ', ';
        foreach ($modules as $module) {
            if ($i <= count($modules)) {
                $breaker = '';
            }
            $result .= $module . $breaker;
        }

        return $result;
    }

    public function setModalContent(int $id, array $params): void
    {
        if($id) {
            $module = $this->resolveRepository()->find($id);
            $this->modalContent = view('modules.web.module.modal-info', compact('module'))->render();
        }
    }
}
