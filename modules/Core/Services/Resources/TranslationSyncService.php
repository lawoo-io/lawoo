<?php

namespace Modules\Core\Services\Resources;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\ModuleUiTranslation;
use Modules\Core\Services\PathService;
use Symfony\Component\Finder\Finder;

class TranslationSyncService
{
    private array $stats = [
        'modules_scanned' => 0,
        'strings_added_to_file' => 0,
        'strings_synced_to_db' => 0,
        'files_updated' => 0,
        'parse_errors' => 0,
        'manual_entries_skipped' => 0,
        'orphaned_strings_removed' => 0,
        'orphaned_strings_marked_removed' => 0,
        'removed_strings_restored' => 0,
        'orphaned_files_cleaned' => 0,
    ];

    /**
     * Synchronisiert Übersetzungsstrings basierend auf der gegebenen Konfiguration
     */
    public function syncTranslations(array $config): array
    {
        $this->resetStats();

        $moduleDirectories = $this->getModuleDirectories($config['modules_base_path'], $config['specific_module']);

        if (empty($moduleDirectories)) {
            return $this->stats;
        }

        // Sammle alle aktuell verwendeten Translation Keys
        $activeKeys = [];

        DB::transaction(function () use ($moduleDirectories, $config, &$activeKeys) {
            foreach ($moduleDirectories as $modulePath) {
                $moduleActiveKeys = $this->processModule($modulePath, $config);
                $moduleName = basename($modulePath);
                $activeKeys[$moduleName] = $moduleActiveKeys;
            }

            // Entferne verwaiste Strings, wenn cleanup aktiviert ist
            if ($config['cleanup_orphaned'] ?? false) {
                $this->cleanupOrphanedStrings($activeKeys, $config);
            }
        });

        return $this->stats;
    }

    /**
     * Findet alle Module-Verzeichnisse
     */
    private function getModuleDirectories(string $basePath, ?string $specificModule = null): array
    {
        $finder = new Finder();
        $finder->in($basePath)
            ->directories()
            ->depth('== 0');

        $moduleDirectories = [];
        foreach ($finder as $directory) {
            $moduleName = $directory->getFilename();
            if (empty($specificModule) || $moduleName === $specificModule) {
                $moduleDirectories[] = $directory->getPathname();
            }
        }

        return $moduleDirectories;
    }

    /**
     * Verarbeitet ein einzelnes Modul
     */
    private function processModule(string $modulePath, array $config): array
    {
        $moduleName = basename($modulePath);
        $this->stats['modules_scanned']++;

        $moduleActiveKeys = [];
        foreach(config('app.scan_directories') as $directory) {
            $viewsPath = $modulePath . '/' . $directory;
            if (!File::isDirectory($viewsPath)) {
                continue;
            }

            $files = $this->findViewFiles($viewsPath);
            if ($files->count() === 0) {
                continue;
            }

            foreach ($files as $file) {
                $fileKeys = $this->processFile($file, $moduleName, $config, $modulePath);
                $moduleActiveKeys = array_merge($moduleActiveKeys, $fileKeys);
            }

        }

        return array_unique($moduleActiveKeys);
    }

    /**
     * Findet alle View-Dateien in einem Verzeichnis
     */
    private function findViewFiles(string $viewsPath): Finder
    {
        return (new Finder())
            ->in($viewsPath)
            ->files()
            ->name(['*.php', '*.blade.php']);
    }

    /**
     * Verarbeitet eine einzelne Datei
     */
    private function processFile(\SplFileInfo $file, string $defaultModule, array $config, string $modulePath = ''): array
    {
        $contents = $file->getContents();
        $translationKeys = $this->extractTranslationKeys($contents);

        if (empty($translationKeys)) {
            return [];
        }

        $fileUpdated = false;
        $activeKeys = [];

        foreach ($translationKeys as $keyData) {
            $targetModule = $keyData['module'] ?: $defaultModule;
            $activeKeys[] = $keyData['key'];

            if ($this->syncTranslationKey($keyData['key'], $targetModule, $config, $modulePath)) {
                $fileUpdated = true;
            }
        }

        if ($fileUpdated) {
            $this->stats['files_updated']++;
        }

        return $activeKeys;
    }

    /**
     * Extrahiert Übersetzungsschlüssel aus Dateiinhalt
     */
    private function extractTranslationKeys(string $contents): array
    {
        $keys = [];

        preg_match_all(
            '/__t\s*\(\s*(["\'])(.*?)\1\s*(?:,\s*(["\'])(.*?)\3\s*)?\)/s',
            $contents,
            $functionMatches,
            PREG_SET_ORDER
        );

        foreach ($functionMatches as $match) {
            $keys[] = [
                'key' => $match[2],
                'module' => isset($match[4]) ? trim($match[4]) : null,
            ];
        }

        return $keys;
    }

    /**
     * Synchronisiert einen einzelnen Übersetzungsschlüssel
     */
    private function syncTranslationKey(string $key, string $module, array $config, string $modulePath = ''): bool
    {

        $updated = false;

        foreach ($config['supported_locales'] as $locale) {
            if ($this->syncToJsonFile($key, $module, $locale, $config, $modulePath)) {
                $updated = true;
            }

            if (!$config['dry_run']) {
                $this->syncToDatabase($key, $module, $locale, $config, $modulePath);
            }
        }

        return $updated;
    }

    /**
     * Synchronisiert zu JSON-Datei
     */
    private function syncToJsonFile(string $key, string $module, string $locale, array $config, string $modulePath = ''): bool
    {
        $jsonFilePath = $this->getJsonFilePath($module, $locale, $modulePath);
        $this->ensureDirectoryExists(dirname($jsonFilePath));

        $jsonContent = $this->loadJsonFile($jsonFilePath);
        if ($jsonContent === null) {
            return false;
        }

        if (isset($jsonContent[$key])) {
            return false; // Schlüssel existiert bereits
        }

        // ALTERNATIVE: Falls Default Locale leer sein soll und andere gefüllt
        // (Ungewöhnlich, aber falls gewünscht)
        $defaultValue = ($locale === $config['default_locale']) ? $key : "";
        $jsonContent[$key] = $defaultValue;

        if (!$config['dry_run']) {
            if (!File::put($jsonFilePath, json_encode($jsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                Log::error("Failed to write to JSON file: {$jsonFilePath}");
                return false;
            }
        }

        $this->stats['strings_added_to_file']++;
        return true;
    }

    /**
     * Synchronisiert zur Datenbank
     */
    private function syncToDatabase(string $key, string $module, string $locale, array $config, string $modulePath = ''): void
    {
        $jsonFilePath = $this->getJsonFilePath($module, $locale, $modulePath);
        $jsonContent = $this->loadJsonFile($jsonFilePath);
        $valueFromJson = $jsonContent[$key] ?? $key;

        $translation = ModuleUiTranslation::where([
            'key_string' => $key,
            'locale' => $locale,
            'module' => $module,
        ])->first();

        if ($translation) {
            // NEUE LOGIK: Prüfen ob String wiederhergestellt werden muss
            if ($translation->removed) {
                $this->restoreRemovedTranslation($translation, $valueFromJson, $config);
            } else {
                $this->handleExistingTranslation($translation, $valueFromJson, $config);
            }
        } else {
            $this->createNewTranslation($key, $module, $locale, $valueFromJson);
        }
    }

    /**
     * Behandelt existierende Übersetzungen
     */
    private function handleExistingTranslation(ModuleUiTranslation $translation, string $valueFromJson, array $config): void
    {
        if (!$translation->is_auto_created && !$config['force_update']) {
            $this->stats['manual_entries_skipped']++;
            return;
        }

        if ($translation->translated_value !== $valueFromJson || $config['force_update']) {
            $translation->update([
                'translated_value' => $valueFromJson,
                'is_auto_created' => !$config['force_update'] ? $translation->is_auto_created : true,
            ]);

            $this->stats['strings_synced_to_db']++;
        }
    }

    /**
     * Stellt einen als entfernt markierten String wieder her
     */
    private function restoreRemovedTranslation(ModuleUiTranslation $translation, string $valueFromJson, array $config): void
    {
        if (!$config['dry_run']) {
            $translation->update([
                'removed' => false,
                'translated_value' => $valueFromJson,
                // is_auto_created bleibt wie es war (manuell bearbeitete bleiben manuell)
            ]);
        }

        $this->stats['removed_strings_restored']++;

        Log::info("Restored previously removed translation", [
            'key' => $translation->key_string,
            'module' => $translation->module,
            'locale' => $translation->locale,
            'was_manual' => !$translation->is_auto_created
        ]);
    }

    /**
     * Erstellt neue Übersetzung
     */
    private function createNewTranslation(string $key, string $module, string $locale, string $value): void
    {
        ModuleUiTranslation::create([
            'key_string' => $key,
            'locale' => $locale,
            'translated_value' => $value,
            'module' => $module,
            'is_auto_created' => true,
        ]);

        $this->stats['strings_synced_to_db']++;
    }

    /**
     * Helper-Methoden
     */
    private function getJsonFilePath(string $module, string $locale, string $modulePath = ''): string
    {
        if (empty($modulePath)) {
            $modulePath = PathService::getModulePath($module);
        }
        return $modulePath . "/Resources/lang/strings/{$locale}.json";
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!File::isDirectory($directory)) {
            if (!File::makeDirectory($directory, 0775, true)) {
                throw new \RuntimeException("Failed to create directory: {$directory}");
            }
        }
    }

    private function loadJsonFile(string $filePath): ?array
    {
        if (!File::exists($filePath)) {
            return [];
        }

        $content = File::get($filePath);
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("JSON parse error in {$filePath}: " . json_last_error_msg());
            $this->stats['parse_errors']++;
            return null;
        }

        return $decoded;
    }

    private function resetStats(): void
    {
        $this->stats = [
            'modules_scanned' => 0,
            'strings_added_to_file' => 0,
            'strings_synced_to_db' => 0,
            'files_updated' => 0,
            'parse_errors' => 0,
            'manual_entries_skipped' => 0,
            'orphaned_strings_removed' => 0,
            'orphaned_strings_marked_removed' => 0,
            'removed_strings_restored' => 0,
            'orphaned_files_cleaned' => 0,
        ];
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * Entfernt verwaiste Translation Keys aus DB und JSON
     */
    private function cleanupOrphanedStrings(array $activeKeysByModule, array $config): void
    {
        foreach ($activeKeysByModule as $moduleName => $activeKeys) {
            $this->cleanupModuleOrphanedStrings($moduleName, $activeKeys, $config);
        }
    }

    /**
     * Bereinigt verwaiste Strings für ein bestimmtes Modul
     */
    private function cleanupModuleOrphanedStrings(string $moduleName, array $activeKeys, array $config): void
    {
        // Hole alle DB-Einträge für dieses Modul
        $dbTranslations = ModuleUiTranslation::where('module', $moduleName)->get();

        // Gruppiere nach key_string
        $dbKeysByString = $dbTranslations->groupBy('key_string');

        foreach ($dbKeysByString as $keyString => $translations) {
            // Wenn der Key nicht mehr in den aktiven Keys ist
            if (!in_array($keyString, $activeKeys)) {
                $this->removeOrphanedKey($keyString, $moduleName, $translations, $config);
            }
        }

        // Bereinige auch JSON-Dateien
        $this->cleanupJsonFiles($moduleName, $activeKeys, $config);
    }

    /**
     * Entfernt einen verwaisten Key aus DB und JSON
     */
    private function removeOrphanedKey(string $keyString, string $moduleName, $translations, array $config): void
    {
        $forceCleanup = $config['force_cleanup'] ?? false;

        // Gruppiere Übersetzungen nach is_auto_created
        $autoCreatedTranslations = $translations->where('is_auto_created', true);
        $manualTranslations = $translations->where('is_auto_created', false);

        if (!$config['dry_run']) {
            // AUTO-CREATED: Immer löschen
            if ($autoCreatedTranslations->isNotEmpty()) {
                ModuleUiTranslation::where('module', $moduleName)
                    ->where('key_string', $keyString)
                    ->where('is_auto_created', true)
                    ->delete();

                $this->stats['orphaned_strings_removed'] += $autoCreatedTranslations->count();
            }

            // MANUAL: Markieren als removed=true oder bei --force-cleanup löschen
            if ($manualTranslations->isNotEmpty()) {
                if ($forceCleanup) {
                    // Mit --force-cleanup: Komplett löschen
                    ModuleUiTranslation::where('module', $moduleName)
                        ->where('key_string', $keyString)
                        ->where('is_auto_created', false)
                        ->delete();

                    $this->stats['orphaned_strings_removed'] += $manualTranslations->count();
                } else {
                    // Standard: Als removed=true markieren
                    ModuleUiTranslation::where('module', $moduleName)
                        ->where('key_string', $keyString)
                        ->where('is_auto_created', false)
                        ->update(['removed' => true]);

                    $this->stats['orphaned_strings_marked_removed'] += $manualTranslations->count();
                }
            }
        } else {
            // Dry-run: Nur Statistiken sammeln
            $this->stats['orphaned_strings_removed'] += $autoCreatedTranslations->count();

            if ($forceCleanup) {
                $this->stats['orphaned_strings_removed'] += $manualTranslations->count();
            } else {
                $this->stats['orphaned_strings_marked_removed'] += $manualTranslations->count();
            }
        }

        // Detailliertes Logging
        if ($autoCreatedTranslations->isNotEmpty()) {
            Log::info("Removed orphaned auto-created translation keys", [
                'key' => $keyString,
                'module' => $moduleName,
                'count' => $autoCreatedTranslations->count(),
                'action' => 'deleted'
            ]);
        }

        if ($manualTranslations->isNotEmpty()) {
            $action = $forceCleanup ? 'deleted' : 'marked_as_removed';
            Log::info("Processed orphaned manual translation keys", [
                'key' => $keyString,
                'module' => $moduleName,
                'count' => $manualTranslations->count(),
                'action' => $action
            ]);
        }
    }

    /**
     * Bereinigt JSON-Dateien von verwaisten Keys
     */
    private function cleanupJsonFiles(string $moduleName, array $activeKeys, array $config): void
    {
        foreach ($config['supported_locales'] as $locale) {
            $jsonFilePath = $this->getJsonFilePath($moduleName, $locale);

            if (!File::exists($jsonFilePath)) {
                continue;
            }

            $jsonContent = $this->loadJsonFile($jsonFilePath);
            if ($jsonContent === null) {
                continue;
            }

            $originalCount = count($jsonContent);
            $updatedContent = [];

            // Nur aktive Keys behalten
            foreach ($jsonContent as $key => $value) {
                if (in_array($key, $activeKeys)) {
                    $updatedContent[$key] = $value;
                }
            }

            $removedCount = $originalCount - count($updatedContent);

            if ($removedCount > 0 && !$config['dry_run']) {
                File::put($jsonFilePath, json_encode($updatedContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $this->stats['orphaned_files_cleaned']++;
            }
        }
    }
}
