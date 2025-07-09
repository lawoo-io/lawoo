<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Web\Models\Setting;
use Modules\Web\Models\SettingsMenu;

class SettingSynchronizerService
{
    /**
     * Synchronizes settings from module configuration files.
     *
     * @param string $moduleName The name of the module (e.g., 'CRM', 'Sales').
     * @return void
     */
    public static function run(string $moduleName): void
    {
        $filePath = PathService::getModulePath($moduleName) . '/Config/Settings.php';
        if (!File::exists($filePath)) {
            return;
        }

        $config = include $filePath;

        foreach ($config as $menuName => $menuData) {
            static::processSettingsMenu($moduleName, $menuName, $menuData);
        }

        // Clean up old settings menus for base modules
        static::cleanupOldSettingsMenus($moduleName, $config);
    }

    /**
     * Processes a single settings menu entry.
     *
     * @param string $moduleName
     * @param string $menuName
     * @param array $menuData
     * @return void
     */
    protected static function processSettingsMenu(string $moduleName, string $menuName, array $menuData): void
    {
        $settingsMenu = SettingsMenu::updateOrCreate(
            ['name' => $menuData['name'] ?? $moduleName, 'module_name' => $moduleName],
            [
                'description' => $menuData['description'] ?? null,
                'icon' => $menuData['icon'] ?? null,
                'middleware' => $menuData['middleware'] ?? null,
                'is_active' => true,
            ]
        );

        static::processSettingsFields($settingsMenu, $menuData['fields'] ?? [], $moduleName);
    }

    /**
     * Processes the fields for a given settings menu.
     *
     * @param SettingsMenu $settingsMenu
     * @param array $fieldsData
     * @return void
     */
    protected static function processSettingsFields(SettingsMenu $settingsMenu, array $fieldsData, string $moduleName): void
    {
        $existingSettingKeys = $settingsMenu->settings()->pluck('key')->toArray();
        $configSettingKeys = [];

        foreach ($fieldsData as $key => $fieldData) {
            $configSettingKeys[] = $key;

            Setting::updateOrCreate(
                [
                    'settings_menu_id' => $settingsMenu->id,
                    'key' => $key,
                ],
                [
                    'value' => $fieldData['value'] ?? null,
                    'module_name' => $moduleName,
                ]
            );
        }

        // Remove settings that are no longer in the config file for this menu
        $keysToRemove = array_diff($existingSettingKeys, $configSettingKeys);
        if (!empty($keysToRemove)) {
            $settingsMenu->settings()->whereIn('key', $keysToRemove)->delete();
        }
    }

    /**
     * Cleans up settings menus that are marked as 'base' and are no longer present in the config.
     * This ensures that if a base settings file is removed, its corresponding menu and settings are deleted.
     *
     * @param string $moduleName
     * @param array $currentConfig
     * @return void
     */
    protected static function cleanupOldSettingsMenus(string $moduleName, array $currentConfig): void
    {
        $key = array_key_first($currentConfig);
        $data = $currentConfig[$key];

        $configs = Setting::where('module_name', $moduleName)->get();

        foreach ($configs as $config) {
            if (!array_key_exists($config->key, $data['fields'])) {
                // If a base menu exists in DB but not in the current config, delete it.
                $config->delete();
            }
        }
    }

    /**
     * Removes all settings and settings menus associated with a specific module.
     * This method should be called when a module is uninstalled or deleted.
     *
     * @param string $moduleName The name of the module to remove settings for.
     * @return void
     */
    public static function removeModuleSettings(string $moduleName, bool $removeDb = false): void
    {
        if ($moduleName === 'Web' && $removeDb) {
            return;
        }

        $settingsMenu = SettingsMenu::where('module_name', $moduleName)->first();

        if (!$settingsMenu) {
            return;
        }

        if ($removeDb) {
            foreach ($settingsMenu->settings() as $setting) {
                $setting->delete();
            }
            $settingsMenu->delete();
        } else {
            $settingsMenu->update(['is_active' => false]);
        }
    }
}
