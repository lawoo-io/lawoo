<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\RBACCleanupService;
use Modules\Core\Contracts\PermissionRegistrarInterface;

class RemoveModuleRBAC extends Command
{
    protected $signature = 'lawoo:rbac:remove {module}
                          {--dry-run : Show what would be removed without making changes}
                          {--force-system : Also remove system permissions/roles}';

    protected $description = 'Remove all RBAC data for a module';

    public function handle(RBACCleanupService $cleanupService, PermissionRegistrarInterface $registrar): int
    {
        $module = $this->argument('module');
        $dryRun = $this->option('dry-run');
        $forceSystem = $this->option('force-system');

        // Zuerst prüfen was vorhanden ist
        $info = $registrar->getModuleRBACInfo($module);

        if ($info['permissions'] === 0 && $info['roles'] === 0) {
            $this->info("No RBAC data found for module: {$module}");
            return Command::SUCCESS;
        }

        // Info anzeigen
        $this->info("RBAC data found for module: {$module}");
        $this->line("  Permissions: {$info['permissions']}");
        $this->line("  Roles: {$info['roles']}");

        if ($info['has_system_data'] && !$forceSystem) {
            $this->warn("  ⚠️  Module contains system permissions/roles");
            $this->line("  Use --force-system to also remove system data");
        }

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No changes will be made");
            $result = $cleanupService->removeModuleRBAC($module, [
                'dry_run' => true,
                'force_system' => $forceSystem
            ]);
            $this->displayDryRunResult($result);
            return Command::SUCCESS;
        }

        // Bestätigung für echte Löschung
        if (!$this->confirmRemoval($module, $forceSystem)) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        // RBAC Daten entfernen
        $this->info("Removing RBAC data for module: {$module}...");

        $result = $cleanupService->removeModuleRBAC($module, [
            'force_system' => $forceSystem
        ]);

        $this->displayResult($result);

        return $result['success'] ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Bestätigung für Löschung
     */
    protected function confirmRemoval(string $module, bool $forceSystem): bool
    {
        $this->warn("This will permanently remove all RBAC data for module: {$module}");

        if ($forceSystem) {
            $this->error('FORCE SYSTEM mode enabled - System permissions/roles will also be removed!');
        }

        return $this->confirm('Are you sure you want to continue?');
    }

    /**
     * Dry Run Ergebnis anzeigen
     */
    protected function displayDryRunResult(array $result): void
    {
        $this->info('Would remove:');
        $this->line("  Permissions: {$result['permissions_found']}");
        $this->line("  Roles: {$result['roles_found']}");
        $this->line("  User assignments: {$result['user_assignments_found']}");

        if (!empty($result['permissions_list'])) {
            $this->line('  Permission slugs: ' . implode(', ', $result['permissions_list']));
        }
        if (!empty($result['roles_list'])) {
            $this->line('  Role slugs: ' . implode(', ', $result['roles_list']));
        }
    }

    /**
     * Ergebnis anzeigen
     */
    protected function displayResult(array $result): void
    {
        if ($result['success']) {
            $this->info('RBAC cleanup completed successfully!');
            $this->line("  Permissions removed: {$result['permissions_removed']}");
            $this->line("  Roles removed: {$result['roles_removed']}");
            $this->line("  User assignments removed: {$result['user_assignments_removed']}");
        } else {
            $this->error('RBAC cleanup failed!');
            if (isset($result['error'])) {
                $this->line("  Error: {$result['error']}");
            }
        }
    }
}
