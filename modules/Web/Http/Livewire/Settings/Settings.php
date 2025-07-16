<?php

namespace Modules\Web\Http\Livewire\Settings;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Core\Services\PathService;
use Modules\Web\Models\Setting;
use Modules\Web\Models\SettingsMenu;

class Settings extends Component
{
    /**
     * Active menu id
     * @var int $id
     */
    #[Url]
    public int $id = 0;

    /**
     * Setting items
     * @var Object $settings
     */
    public array $data = [];

    /**
     * @var Object $settingsMenus
     */
    public array $settingsMenus = [];

    /**
     * @var array $fields
     */
    public array $fields = [];

    public array $rules = [];

    public function mount(): void
    {
        $this->settingsMenus = Cache::tags(['table:settings_menus'])->remember('settings_menu_' . app()->getLocale(), now()->addDays(intval(settings('cache_settings_records'))), function() {
            return SettingsMenu::isActive()->get()->map(function($menu){
                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'icon' => $menu->icon,
                    'middleware' => $menu->middleware,
                ];
            })->toArray();
        });

//        $this->settingsMenus = SettingsMenu::isActive()->get()->map(function($menu){
//            return [
//                'id' => $menu->id,
//                'name' => $menu->name,
//                'icon' => $menu->icon,
//                'middleware' => $menu->middleware,
//            ];
//        })->toArray();

        $this->setActive();
        $this->getSettings();
    }

    protected function setActive(): void
    {
        if(!$this->id){
            $this->id = SettingsMenu::where('module_name', 'Web')->first()->id;
        }

        $this->getSettings();
    }

    protected function getSettings(): void
    {
        $this->data = Cache::tags(['table:settings'])->remember('settings_records_' . $this->id, now()->addDays(intval(settings('cache_settings_records'))), function(){
            return Setting::where('settings_menu_id', $this->id)->get()->toArray();
        });

        $this->getFields();
        $this->prepareData();
        $this->prepareRules();
    }

    protected function prepareData(): void
    {
        $data = [];
        foreach ($this->data as $setting) {
            $data[$setting['key']] = $setting['value'] === '1' ? true : $setting['value'];
        }
        $this->data = $data;
    }

    protected function getFields(): array
    {
        foreach ($this->data as $setting) {
            $basePath = PathService::getModulePath($setting['module_name']);
            $filePath = $basePath . "/Config/Settings.php";
            $fields = $this->getFieldsFromFile($filePath);
            if($fields) $this->fields = array_merge($this->fields, $fields[$setting['module_name']]['fields']);
        }

        return [];
    }

    protected function prepareRules(): void
    {
        foreach ($this->fields as $field => $setting) {
            if (isset($setting['rules'])) {
                $this->rules['data.'.$field] = $setting['rules'];
            }
        }
    }

    public function save(): void
    {
        if($this->rules) $this->validate($this->rules);

        foreach ($this->data as $field => $value) {
            Setting::where('key', $field)->firstOrFail()->update(['value' => $value]);
        }
    }

    protected function getFieldsFromFile(string $path)
    {
        if(file_exists($path)){
            return include $path;
        }
        return [];
    }

    public function render()
    {
        return view('livewire.web.settings.settings');
    }
}
