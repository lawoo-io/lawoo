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
        $sourceBase = base_path('vendor/lawoo-io/lawoo/modules');
        $targetBase = base_path('modules');

        if (!File::exists($sourceBase)) {
            $this->error("❌ Lawoo modules not found.");
            return;
        }

        File::ensureDirectoryExists($targetBase);

        $modules = File::directories($sourceBase);

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $targetPath = $targetBase . DIRECTORY_SEPARATOR . $moduleName;

            if (File::exists($targetPath)) {
                $this->warn("⚠️  $moduleName already exists – skipped.");
                continue;
            }

            File::copyDirectory($modulePath, $targetPath);
            $this->info("✅ Installed: $moduleName");
        }

        $this->info("🎉 All Lawoo modules installed.");
    }
}