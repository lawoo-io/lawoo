<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class InitCommand extends Command
{
    protected $signature = 'lawoo:init';
    protected $description = 'Install all Lawoo modules into /modules';

    public function handle()
    {
        try {

            $this->info("Start Lawoo Installation");
            // 1. NPM Dependencies
            $this->installNpmDependencies();

            $targetBase = base_path('modules');

            File::ensureDirectoryExists($targetBase);

            $this->info("Modules directory created in /modules directory");

        } catch (\Exception $e) {
            $this->error('❌ Installation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function installNpmDependencies(): void
    {
        $this->info('📦 Installing NPM dependencies...');

        if ($this->option('fresh')) {
            $this->info('🧹 Cleaning node_modules...');
            Process::run('rm -rf node_modules package-lock.json');
        }

        $result = Process::run('npm install');

        if (!$result->successful()) {
            throw new \Exception('NPM install failed: ' . $result->errorOutput());
        }

        $result = Process::run('npm install glightbox');

        if (!$result->successful()) {
            throw new \Exception('GLightbox install failed: ' . $result->errorOutput());
        }

        $this->info('✅ NPM dependencies installed');
    }
}
