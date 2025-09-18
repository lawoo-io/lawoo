<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class InitCommand extends Command
{
    protected $signature = 'lawoo:init {--f|fresh : Cleaning node_modules}';
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

            Artisan::call("migrate --seed");
            Artisan::call("lawoo:check");
            Artisan::call("lawoo:install Web");

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

        // NPM Install
        $result = Process::run('npm install');

        if (!$result->successful()) {
            throw new \Exception('NPM install failed: ' . $result->errorOutput());
        }

        // GLightbox installation
        $result = Process::run('npm install glightbox');

        if (!$result->successful()) {
            throw new \Exception('GLightbox install failed: ' . $result->errorOutput());
        }

        // Install Codemirror
        $result = Process::run('npm install codemirror@6.0.1 @codemirror/state@6.5.2 @codemirror/view@6.38.1 @codemirror/language@6.11.3 @codemirror/lang-html@6.4.9 @codemirror/lang-css@6.3.1 @codemirror/lang-javascript@6.2.4 @codemirror/theme-one-dark@6.1.3');

        if (!$result->successful()) {
            throw new \Exception('Codemirror install failed: ' . $result->errorOutput());
        }

        // Install glightbox
        $result = Process::run('npm install glightbox');
        if (!$result->successful()) {
            throw new \Exception('Glightbox install failed: ' . $result->errorOutput());
        }

        $this->info('✅ NPM dependencies installed');
    }
}
