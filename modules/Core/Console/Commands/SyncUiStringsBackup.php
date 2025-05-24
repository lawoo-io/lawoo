<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\ModuleUiTranslation;
use Symfony\Component\Finder\Finder;

class SyncUiStringsBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lawoo:sync-ui-strings {module? : The name of a specific module to sync (e.g., Core, CRM). If omitted, all modules are synced.} {--force : Overwrites ALL database entries for found strings, even manually modified ones.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting UI string discovery and direct database synchronization...');

        $specificModule = $this->argument('module');

        $modulesBasePath = base_path('modules');
        if (!File::exists($modulesBasePath)) {
            $this->error("The modules directory '{$modulesBasePath}' does not exist. No modules to scan.");
            return Command::FAILURE;
        }

        $defaultLocale = config('app.locale'); // Get the default locale (e.g., 'de')
        $supportedLocales = array_keys(config('app.locales'));
        if (empty($supportedLocales)) {
            $this->error("No supported locales defined in config/app.php. Cannot discover translations.");
            return Command::FAILURE;
        }

        $stringsAddedToFile = 0;
        $stringsAddedToDb = 0;
        $filesUpdatedCount = 0;
        $modulesScannedCount = 0;
        $parseErrorsCount = 0;
        $dbSkippedManualCount = 0;
        $fileErrorsCount = 0;


        $finder = new Finder();
        $finder->in($modulesBasePath)
            ->directories()
            ->depth('== 0');

        $moduleDirectories = [];
        foreach ($finder as $directory) {
            $moduleName = basename($directory->getPathname());
            if (empty($specificModule) || $moduleName === $specificModule) {
                $moduleDirectories[] = $directory->getPathname();
            }
        }

        if (empty($moduleDirectories)) {
            if (!empty($specificModule)) {
                $this->error("Specific module '{$specificModule}' not found in '{$modulesBasePath}'.");
            } else {
                $this->info("No module directories found in '{$modulesBasePath}'. Nothing to discover.");
            }
            return Command::SUCCESS;
        }

        // Use a database transaction for atomicity in DB operations
        DB::transaction(function () use (
            $moduleDirectories,
            $supportedLocales,
            $defaultLocale,
            &$stringsAddedToFile,
            &$stringsAddedToDb,
            &$filesUpdatedCount,
            &$modulesScannedCount,
            &$parseErrorsCount,
            &$dbSkippedManualCount
        ) {
            foreach ($moduleDirectories as $modulePath) {
                $moduleName = basename($modulePath);
                $this->comment("Processing module: <info>{$moduleName}</info>");
                $modulesScannedCount++;

                $moduleViewsPath = $modulePath . '/Resources/views';
                if (!File::isDirectory($moduleViewsPath)) {
                    $this->line("  - No views directory found for module '{$moduleName}' at: <comment>{$moduleViewsPath}</comment>. Skipping view scan.");
                    continue;
                }

                $fileExtensions = ['*.php', '*.blade.php'];

                $moduleViewsFinder = new Finder();
                $moduleViewsFinder->in($moduleViewsPath)
                    ->files()
                    ->name($fileExtensions);

                if (!$moduleViewsFinder->hasResults()) {
                    $this->line("  - No relevant view files found in '{$moduleViewsPath}'. Skipping discovery for this module.");
                    continue;
                }

                foreach ($moduleViewsFinder as $file) {
                    $contents = $file->getContents();
                    $fileUpdated = false;

                    preg_match_all('/@_t\s*\(\s*(["\'])(.*?)\1\s*(?:,\s*(["\'])(.*?)\3\s*)?\)/s', $contents, $matches, PREG_SET_ORDER);

                    if (empty($matches)) {
                        continue;
                    }

                    $this->line("  Scanning file: <info>{$file->getRelativePathname()}</info>");

                    foreach ($matches as $match) {
                        $keyString = $match[2];
                        $extractedModuleName = isset($match[4]) ? $match[4] : '';

                        $targetModuleName = (!empty($extractedModuleName)) ? trim($extractedModuleName) : $moduleName;

                        $moduleLangStringsPath = base_path('modules') . '/' . $targetModuleName . '/Resources/lang/strings';

                        $dir = dirname($moduleLangStringsPath . '/temp.json');
                        if (!File::isDirectory($dir)) {
                            try {
                                File::makeDirectory($dir, 0775, true);
                                $this->line("    - Created directory: <comment>{$dir}</comment>");
                            } catch (\Exception $e) {
                                $this->error("    - Failed to create directory '{$dir}': " . $e->getMessage());
                                Log::error("Failed to create module translation directory during discovery: {$dir} - " . $e->getMessage());
                                $parseErrorsCount++;
                                continue;
                            }
                        }

                        foreach ($supportedLocales as $locale) {
                            $jsonFilePath = $moduleLangStringsPath . '/' . $locale . '.json';

                            $jsonContent = [];
                            if (File::exists($jsonFilePath)) {
                                $content = File::get($jsonFilePath);
                                $jsonContent = json_decode($content, true);
                                if (json_last_error() !== JSON_ERROR_NONE) {
                                    $this->error("    - Error parsing JSON file (skipping): {$jsonFilePath} - " . json_last_error_msg());
                                    Log::error("JSON parse error in {$jsonFilePath}: " . json_last_error_msg());
                                    $parseErrorsCount++;
                                    $jsonContent = [];
                                }
                            }

                            // Determine the default value for the JSON file based on the locale.
                            // Standard language (e.g., 'de'): key = value
                            // Other languages: key = ""
                            $defaultValueForJson = ($locale === $defaultLocale) ? $keyString : "";

                            $stringWasAddedToFile = false;
                            if (!isset($jsonContent[$keyString])) {
                                $jsonContent[$keyString] = $defaultValueForJson; // Use the determined default for JSON file
                                File::put($jsonFilePath, json_encode($jsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                $this->line("    -> Added key '<info>{$keyString}</info>' to <comment>{$jsonFilePath}</comment> with default '{$defaultValueForJson}'");
                                $stringsAddedToFile++;
                                $stringWasAddedToFile = true;
                                $fileUpdated = true;
                            }

                            // --- KORREKTUR: DB-Synchronisation (Modulname, translated_value) ---
                            // Der Wert, der aus der JSON-Quelle in die DB geschrieben werden soll.
                            // Dies ist der Wert, der im JSON-Array für den aktuellen keyString und die aktuelle Locale steht.
                            $valueToSyncToDb = isset($jsonContent[$keyString]) ? $jsonContent[$keyString] : $keyString;
                            // Falls der String in der JSON-Datei nicht gefunden wurde (was nach dem vorherigen Block unwahrscheinlich ist,
                            // es sei denn, er wurde gerade hinzugefügt und ist der KeyString), nehmen wir den KeyString als Fallback.

                            $translationInDb = ModuleUiTranslation::where('key_string', $keyString)
                                ->where('locale', $locale)
                                ->where('module', $targetModuleName) // <-- WICREHTIG: Sicherstellen, dass hier $targetModuleName verwendet wird
                                ->first();

                            if ($translationInDb) {
                                // Eintrag existiert in DB. Aktualisiere, wenn auto-created ODER wenn der Wert im JSON anders ist UND die DB auto-created ist.
                                // Die JSON-Datei ist die Quelle der Wahrheit.
                                if ($translationInDb->is_auto_created) {
                                    // Wenn der Wert im JSON anders ist ODER der String gerade erst hinzugefügt wurde (und somit neu ist)
                                    // WICHTIG: Überschreibt nur, wenn auto_created=true.
                                    $translationInDb->translated_value = $valueToSyncToDb; // <-- Wert aus JSON in DB schreiben
                                    $translationInDb->is_auto_created = true; // Bleibt auto-created
                                    $translationInDb->save();
                                    $stringsAddedToDb++;
                                    $this->line("    -> Updated key '<info>{$keyString}</info>' in DB for <comment>{$targetModuleName}/{$locale}</comment> (from JSON).");
                                } else {
                                    // Manuell im Admin-Panel geändert, NICHT überschreiben.
                                    $this->line("    -> Skipping DB update for '<comment>{$keyString}</comment>' in <comment>{$targetModuleName}/{$locale}</comment> (manually modified).");
                                    $dbSkippedManualCount++;
                                }
                            } else {
                                // Eintrag existiert nicht in DB -> Neu anlegen.
                                ModuleUiTranslation::create([
                                    'key_string' => $keyString,
                                    'locale' => $locale,
                                    'translated_value' => $valueToSyncToDb, // <-- Wert aus JSON in DB schreiben
                                    'module' => $targetModuleName, // <-- WICHTIG: Sicherstellen, dass hier $targetModuleName verwendet wird
                                    'is_auto_created' => true,
                                ]);
                                $stringsAddedToDb++;
                                $this->line("    -> Created key '<info>{$keyString}</info>' in DB for <comment>{$targetModuleName}/{$locale}</comment> (from JSON).");
                            }
                            // --- ENDE KORREKTUR ---
                        }
                    }
                    if ($fileUpdated) {
                        $filesUpdatedCount++;
                    }
                }
            }
        }); // End of DB transaction

        // Clear cache for translations after all updates.
        if (function_exists('clear_t_cache')) {
            clear_t_cache(); // Clear all translation cache
            $this->info('Translation cache cleared.');
        } else {
            $this->warn('Warning: clear_t_cache() function not found. Please ensure it is loaded.');
            Cache::flush();
        }

        $this->info("Discovery and synchronization completed.");
        $this->info("  - Modules scanned: <info>{$modulesScannedCount}</info>");
        $this->info("  - New keys added to JSON files: <info>{$stringsAddedToFile}</info>");
        $this->info("  - Total keys synchronized to DB: <info>{$stringsAddedToDb}</info>");
        $this->info("  - Manually modified entries in DB skipped: <comment>{$dbSkippedManualCount}</comment>");
        if ($fileErrorsCount > 0) {
            $this->error("  - Encountered <error>{$fileErrorsCount}</error> JSON parsing errors. Check logs.");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
