<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Core\Services\PathService;
use Modules\Core\Services\Resources\TranslationSyncService;

class SyncUiStrings extends Command
{
    protected $signature = 'lawoo:sync-ui-strings
                           {module? : The name of a specific module to sync (e.g., Core, CRM)}
                           {--force : Overwrites ALL database entries for found strings}
                           {--cleanup : Remove orphaned translation keys from DB and JSON}
                           {--force-cleanup : Also remove manually modified orphaned entries}
                           {--dry-run : Show what would be changed without making changes}';

    protected $description = 'Synchronize UI translation strings from Blade templates to JSON files and database';

    public function __construct(private TranslationSyncService $syncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting UI string discovery and synchronization...');

        try {
            $config = $this->prepareConfiguration();

            if ($config['dry_run']) {
                $this->warn('🔍 DRY RUN MODE - No changes will be made');
            }

            $stats = $this->syncService->syncTranslations($config);

            $this->clearTranslationCache();
            $this->displayResults($stats);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Command failed: {$e->getMessage()}");
            Log::error('SyncUiStrings command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    private function prepareConfiguration(): array
    {
        $specificModule = $this->argument('module');
        $modulesBasePath = PathService::getByModule($specificModule);

        if (!File::exists($modulesBasePath)) {
            throw new \RuntimeException("The modules directory '{$modulesBasePath}' does not exist.");
        }

        $supportedLocales = array_keys(config('app.locales', []));
        if (empty($supportedLocales)) {
            throw new \RuntimeException("No supported locales defined in config/app.php.");
        }

        if ($specificModule && !File::isDirectory("{$modulesBasePath}/{$specificModule}")) {
            throw new \RuntimeException("Specific module '{$specificModule}' not found.");
        }

        return [
            'modules_base_path' => $modulesBasePath,
            'specific_module' => $specificModule,
            'default_locale' => config('app.locale'),
            'supported_locales' => $supportedLocales,
            'force_update' => $this->option('force'),
            'cleanup_orphaned' => $this->option('cleanup'),
            'force_cleanup' => $this->option('force-cleanup'),
            'dry_run' => $this->option('dry-run'),
        ];
    }

    private function clearTranslationCache(): void
    {
        if (function_exists('clear_t_cache')) {
            clear_t_cache();
            $this->info('✅ Translation cache cleared via clear_t_cache().');
        } else {
            Cache::flush();
            $this->info('✅ All cache cleared via Cache::flush().');
        }
    }

    private function displayResults(array $stats): void
    {
        $this->newLine();
        $this->info('🎉 Synchronization completed successfully!');

        $this->table(
            ['📊 Metric', 'Count'],
            [
                ['🔍 Modules scanned', $stats['modules_scanned']],
                ['📝 New keys added to JSON files', $stats['strings_added_to_file']],
                ['💾 Keys synchronized to DB', $stats['strings_synced_to_db']],
                ['📄 Files updated', $stats['files_updated']],
                ['⏭️  Manual entries skipped', $stats['manual_entries_skipped']],
                ['🗑️  Orphaned strings removed', $stats['orphaned_strings_removed']],
                ['🏷️  Manual strings marked as removed', $stats['orphaned_strings_marked_removed']],
                ['🔄 Removed strings restored', $stats['removed_strings_restored']],
                ['🧹 JSON files cleaned', $stats['orphaned_files_cleaned']],
                ['❌ Parse errors', $stats['parse_errors']],
            ]
        );

        if ($stats['parse_errors'] > 0) {
            $this->warn("⚠️  Encountered {$stats['parse_errors']} parse errors. Check the logs for details.");
        }

        if ($stats['modules_scanned'] === 0) {
            $this->warn('⚠️  No modules were processed. Check your module directory structure.');
        }
    }
}
