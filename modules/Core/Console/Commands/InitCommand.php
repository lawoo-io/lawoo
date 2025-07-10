<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InitCommand extends Command
{
    protected $signature = 'lawoo:init';
    protected $description = 'Install all Lawoo modules into /modules';

    public function handle()
    {
        $targetBase = base_path('modules');

        File::ensureDirectoryExists($targetBase);

        $this->info("🎉 All Lawoo modules installed.");
    }
}
