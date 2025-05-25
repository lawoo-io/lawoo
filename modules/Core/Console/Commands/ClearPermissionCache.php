<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Contracts\PermissionRegistrarInterface;

class ClearPermissionCache extends Command
{
    protected $signature = 'lawoo:rbac:clear-cache';
    protected $description = 'Clear RBAC permission cache';

    public function handle(PermissionRegistrarInterface $registrar): int
    {
        $registrar->clearCache();

        $this->info('RBAC cache cleared successfully!');

        return Command::SUCCESS;
    }
}
