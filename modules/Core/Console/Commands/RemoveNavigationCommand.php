<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Core\Services\NavigationService;
use Modules\Core\Services\PathService;

class RemoveNavigationCommand extends Command
{
    protected $signature = 'lawoo:nav:remove
                           {module : Specific module to remove}
                           {--dry-run : Show what would be removed without making changes}
                           {--force : Force removal without confirmation}';

    protected $description = 'Remove navigation items from database for specific module';

    public function __construct(private NavigationService $navigationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting navigation removal...');

        try {
            $config = $this->prepareConfiguration();

            if ($config['dry_run']) {
                $this->warn('🔍 DRY RUN MODE - No changes will be made');
                return $this->dryRun($config);
            }

            if (!$config['force'] && !$this->confirmRemoval($config)) {
                $this->info('Operation cancelled.');
                return \Illuminate\Console\Command::SUCCESS;
            }

            $stats = $this->removeNavigation($config);
            $this->displayResults($stats);

            return \Illuminate\Console\Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Command failed: {$e->getMessage()}");
            Log::error('RemoveNavigation command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return \Illuminate\Console\Command::FAILURE;
        }
    }

    private function prepareConfiguration(): array
    {
        $module = $this->argument('module');

        $modulesBasePath = PathService::getByModule($module);

        if (!File::exists($modulesBasePath)) {
            throw new \RuntimeException("The modules directory '{$modulesBasePath}' does not exist.");
        }

        if (!$module) {
            throw new \RuntimeException("Module name is required for removal.");
        }

        if (!File::isDirectory("{$modulesBasePath}/{$module}")) {
            throw new \RuntimeException("Module '{$module}' not found.");
        }

        return [
            'modules_base_path' => $modulesBasePath,
            'module' => $module,
            'dry_run' => $this->option('dry-run'),
            'force' => $this->option('force'),
        ];
    }

    private function removeNavigation(array $config): array
    {
        $result = $this->navigationService->removeModule($config['module']);

        return [
            'module' => $result['module'],
            'items_removed' => $result['removed_items'],
            'relationships_cleaned' => 0, // Not tracked yet
            'user_modified_removed' => 0, // Not tracked yet
            'errors' => 0,
        ];
    }

    private function dryRun(array $config): int
    {
        $this->info('🔍 Dry run - showing what would be removed...');

        $module = $config['module'];
        $count = \Modules\Core\Models\Navigation::where('module', $module)->count();
        $userModifiedCount = \Modules\Core\Models\Navigation::where('module', $module)
            ->where('is_user_modified', true)
            ->count();

        $stats = [
            'module' => $module,
            'items_to_remove' => $count,
            'relationships_to_clean' => 0, // Would need to calculate
            'user_modified_items' => $userModifiedCount,
        ];

        $this->displayDryRunResults($stats);

        return \Illuminate\Console\Command::SUCCESS;
    }

    private function confirmRemoval(array $config): bool
    {
        $module = $config['module'];
        $count = \Modules\Core\Models\Navigation::where('module', $module)->count();

        if ($count === 0) {
            $this->info("No navigation items found for module '{$module}'.");
            return false;
        }

        return $this->confirm("Remove {$count} navigation items from module '{$module}'?");
    }

    private function displayResults(array $stats): void
    {
        $this->newLine();
        $this->info('🎉 Navigation removal completed successfully!');

        $this->table(
            ['📊 Metric', 'Count'],
            [
                ['🗑️  Module processed', $stats['module']],
                ['📝 Items removed', $stats['items_removed']],
                ['🔗 Relationships cleaned', $stats['relationships_cleaned']],
                ['⚠️  User-modified items removed', $stats['user_modified_removed']],
                ['❌ Errors encountered', $stats['errors']],
            ]
        );

        if ($stats['errors'] > 0) {
            $this->warn("⚠️  Encountered {$stats['errors']} errors during removal. Check the logs for details.");
        }

        if ($stats['items_removed'] === 0) {
            $this->warn('⚠️  No navigation items were removed.');
        }
    }

    private function displayDryRunResults(array $stats): void
    {
        $this->newLine();
        $this->info('📋 Dry Run Results:');

        $this->table(
            ['📊 Preview', 'Count'],
            [
                ['🗑️  Module to process', $stats['module']],
                ['📝 Items to remove', $stats['items_to_remove']],
                ['🔗 Relationships to clean', $stats['relationships_to_clean']],
                ['⚠️  User-modified items', $stats['user_modified_items']],
            ]
        );

        if ($stats['items_to_remove'] === 0) {
            $this->info("No navigation items found for module '{$stats['module']}'.");
        }
    }
}
