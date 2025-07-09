<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Modules\Core\Services\NavigationService;
use Modules\Core\Services\PathService;

class SyncNavigationCommand extends Command
{

    protected $signature = 'lawoo:nav:sync
                           {module? : Specific module to sync}
                           {--validate : Only validate config without syncing}
                           {--dry-run : Show what would be done without making changes}';

    protected $description = 'Synchronize navigation config files to database';

    public function __construct(private NavigationService $navigationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting navigation synchronization...');

        try {
            $config = $this->prepareConfiguration();

            if ($config['validate_only']) {
                return $this->validateConfigs($config);
            }

            if ($config['dry_run']) {
                $this->warn('🔍 DRY RUN MODE - No changes will be made');
                $this->warn('Dry run functionality not yet implemented');
                return \Illuminate\Console\Command::SUCCESS;
            }

            $stats = $this->syncNavigation($config);
            $this->displayResults($stats);

            return \Illuminate\Console\Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Command failed: {$e->getMessage()}");
            Log::error('SyncNavigation command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return \Illuminate\Console\Command::FAILURE;
        }
    }

    private function prepareConfiguration(): array
    {
        $specificModule = $this->argument('module');
        $modulesBasePath = PathService::getByModule($specificModule);

        if (!File::exists($modulesBasePath)) {
            throw new \RuntimeException("The modules directory '{$modulesBasePath}' does not exist.");
        }

        if ($specificModule && !File::isDirectory("{$modulesBasePath}/{$specificModule}")) {
            throw new \RuntimeException("Specific module '{$specificModule}' not found.");
        }

        if ($specificModule) {
            $configPath = "{$modulesBasePath}/{$specificModule}/Config/Navigation.php";
            if (!File::exists($configPath)) {
                throw new \RuntimeException("Navigation config not found for module '{$specificModule}'.");
            }
        }

        return [
            'modules_base_path' => $modulesBasePath,
            'specific_module' => $specificModule,
            'validate_only' => $this->option('validate'),
            'dry_run' => $this->option('dry-run'),
        ];
    }

    private function syncNavigation(array $config): array
    {
        if ($config['specific_module']) {
            $result = $this->navigationService->syncModule($config['specific_module']);
            return [
                'modules_processed' => 1,
                'items_processed' => $result['processed'],
                'items_created' => $result['created'],
                'items_updated' => $result['updated'],
                'items_skipped' => $result['skipped'],
                'relationships_updated' => 0, // Not tracked yet
                'config_errors' => 0,
            ];
        } else {
            $results = $this->navigationService->syncAllModules();
            $totals = [
                'modules_processed' => count($results),
                'items_processed' => 0,
                'items_created' => 0,
                'items_updated' => 0,
                'items_skipped' => 0,
                'relationships_updated' => 0,
                'config_errors' => 0,
            ];

            foreach ($results as $result) {
                $totals['items_processed'] += $result['processed'];
                $totals['items_created'] += $result['created'];
                $totals['items_updated'] += $result['updated'];
                $totals['items_skipped'] += $result['skipped'];
            }

            return $totals;
        }
    }

    private function validateConfigs(array $config): int
    {
        $this->info('🔍 Validating navigation configs...');

        $hasErrors = false;
        $stats = [
            'modules_validated' => 0,
            'valid_configs' => 0,
            'invalid_configs' => 0,
            'total_errors' => 0,
            'errors' => [],
        ];

        if ($config['specific_module']) {
            $errors = $this->navigationService->validateConfig($config['specific_module']);
            $stats['modules_validated'] = 1;

            if (!empty($errors)) {
                $stats['invalid_configs'] = 1;
                $stats['total_errors'] = count($errors);
                $stats['errors'][$config['specific_module']] = $errors;
                $hasErrors = true;
            } else {
                $stats['valid_configs'] = 1;
                $this->info("✅ {$config['specific_module']}: Config is valid");
            }
        } else {
            $modules = $this->navigationService->getAvailableModules();
            $stats['modules_validated'] = count($modules);

            foreach ($modules as $module) {
                $errors = $this->navigationService->validateConfig($module);
                if (!empty($errors)) {
                    $stats['invalid_configs']++;
                    $stats['total_errors'] += count($errors);
                    $stats['errors'][$module] = $errors;
                    $hasErrors = true;
                } else {
                    $stats['valid_configs']++;
                    $this->info("✅ {$module}: Config is valid");
                }
            }
        }

        $this->displayValidationResults($stats);

        if ($hasErrors) {
            $this->error('❌ Validation failed with errors');
            return \Illuminate\Console\Command::FAILURE;
        }

        $this->info('✅ All navigation configs are valid!');
        return \Illuminate\Console\Command::SUCCESS;
    }

    private function displayResults(array $stats): void
    {
        $this->newLine();
        $this->info('🎉 Navigation synchronization completed successfully!');

        $this->table(
            ['📊 Metric', 'Count'],
            [
                ['🔍 Modules processed', $stats['modules_processed']],
                ['📝 Items processed', $stats['items_processed']],
                ['✨ Items created', $stats['items_created']],
                ['🔄 Items updated', $stats['items_updated']],
                ['⏭️  Items skipped (user modified)', $stats['items_skipped']],
                ['🏷️  Parent relationships updated', $stats['relationships_updated']],
                ['❌ Config errors', $stats['config_errors']],
            ]
        );

        if ($stats['config_errors'] > 0) {
            $this->warn("⚠️  Encountered {$stats['config_errors']} config errors. Check the logs for details.");
        }

        if ($stats['modules_processed'] === 0) {
            $this->warn('⚠️  No modules were processed. Check your module directory structure.');
        }
    }

    private function displayValidationResults(array $stats): void
    {
        $this->newLine();

        $this->table(
            ['📊 Validation Results', 'Count'],
            [
                ['🔍 Modules validated', $stats['modules_validated']],
                ['✅ Valid configs', $stats['valid_configs']],
                ['❌ Configs with errors', $stats['invalid_configs']],
                ['🚫 Total errors', $stats['total_errors']],
            ]
        );

        if (!empty($stats['errors'])) {
            $this->newLine();
            $this->error('Validation Errors:');
            foreach ($stats['errors'] as $module => $errors) {
                $this->line("<fg=red>❌ {$module}:</fg=red>");
                foreach ($errors as $error) {
                    $this->line("   • {$error}");
                }
            }
        }
    }
}
