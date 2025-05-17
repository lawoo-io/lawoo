<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\Modules\ModuleChecker;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ModulesCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:check {module?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check module(s) in modules/ directory and add or update them in the database';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = $this->argument('module');

        $result = ModuleChecker::run($module);

        $this->components->{$result['type']}($result['message']);
    }
}
