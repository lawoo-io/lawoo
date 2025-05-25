<?php

namespace Modules\Core\Traits;

use Modules\Core\Contracts\PermissionRegistrarInterface;

trait RegistersRBAC
{
    /**
     * Register RBAC from PHP config file
     *
     * @param string $configPath Path to RolesAndPermissions.php
     * @param string $module Module name (lowercase)
     */
    protected function registerRBACFromConfig(string $configPath, string $module): void
    {
        // Nur in Web-Context, nicht bei Artisan Commands
        if ($this->app->runningInConsole()) {
            return;
        }

        // Config-File existiert?
        if (!file_exists($configPath)) {
            return;
        }

        try {
            // PHP Config laden
            $rolesConfig = require $configPath;

            if (empty($rolesConfig)) {
                return;
            }

            $registrar = app(PermissionRegistrarInterface::class);

            // Alle Rollen & Permissions registrieren
            foreach ($rolesConfig as $roleData) {
                $this->processConfigRole($registrar, $roleData, $module);
            }

        } catch (\Exception $e) {
            \Log::error("Error parsing PHP RBAC config for module {$module}: " . $e->getMessage());
        }
    }

    /**
     * Register RBAC from array (direkt im Service Provider definiert)
     */
    protected function registerRBACFromArray(array $rolesConfig, string $module): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $registrar = app(PermissionRegistrarInterface::class);

        foreach ($rolesConfig as $roleData) {
            $this->processConfigRole($registrar, $roleData, $module);
        }
    }

    /**
     * Process single role from config
     */
    private function processConfigRole(PermissionRegistrarInterface $registrar, array $roleData, string $module): void
    {
        // 1. Permissions extrahieren und registrieren
        $permissionSlugs = [];

        if (isset($roleData['permissions'])) {
            foreach ($roleData['permissions'] as $slug => $permissionData) {
                // Permission-Daten normalisieren
                $permissionRecord = $this->normalizePermissionData($slug, $permissionData, $module);

                // Permission registrieren
                $registrar->registerPermissions(strtolower($module), [$permissionRecord]);
                $permissionSlugs[] = $slug;
            }
        }

        // 2. Rolle erstellen
        $roleRecord = [
            'slug' => $roleData['slug'],
            'name' => $roleData['name'],
            'description' => $roleData['description'] ?? '',
            'module' => strtolower($module),
            'is_system' => $roleData['is_system'] ?? false,
            'permissions' => $permissionSlugs
        ];

        $registrar->createRoleWithPermissions(strtolower($module), $roleRecord);
    }

    /**
     * Normalize permission data from config
     */
    private function normalizePermissionData(string $slug, $permissionData, string $module): array
    {
        // Wenn Permission-Data ein Array ist
        if (is_array($permissionData)) {
            return [
                'slug' => $slug,
                'name' => $permissionData['name'] ?? $this->generatePermissionName($slug),
                'description' => $permissionData['description'] ?? "Permission: {$slug}",
                'module' => strtolower($module),
                'resource' => $permissionData['resource'] ?? $this->extractResource($slug),
                'action' => $permissionData['action'] ?? $this->extractAction($slug),
                'is_system' => $permissionData['is_system'] ?? false
            ];
        }

        // Fallback für einfache Werte (String oder NULL)
        return [
            'slug' => $slug,
            'name' => $this->generatePermissionName($slug),
            'description' => "Permission: {$slug}",
            'module' => strtolower($module),
            'resource' => $this->extractResource($slug),
            'action' => $this->extractAction($slug),
            'is_system' => false
        ];
    }

    /**
     * Generate human-readable permission name from slug
     */
    private function generatePermissionName(string $slug): string
    {
        // crm.contacts.view → View Contacts
        $parts = explode('.', $slug);
        if (count($parts) >= 3) {
            $action = ucfirst($parts[2]);
            $resource = ucfirst($parts[1]);
            return "{$action} {$resource}";
        }

        return ucwords(str_replace(['.', '_', '-'], ' ', $slug));
    }

    /**
     * Extract resource from slug
     */
    private function extractResource(string $slug): string
    {
        $parts = explode('.', $slug);
        return $parts[1] ?? 'general';
    }

    /**
     * Extract action from slug
     */
    private function extractAction(string $slug): string
    {
        $parts = explode('.', $slug);
        return $parts[2] ?? 'access';
    }
}
