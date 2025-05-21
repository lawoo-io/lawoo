<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\Resources\ResourceBuild;
use RuntimeException;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ModulesResBuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lawoo:build-resources {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the resources in /modules/ModuleName/Resources and add, update, or remove their metadata in the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = $this->argument('module');

        try {
            if ($module !== '*') {
                $moduleNames = explode(' ', $module);
            } else {
                $moduleNames = '*';
            }

            $result = ResourceBuild::run($moduleNames);
            $this->components->{$result['type']}($result['message']);

        } catch (RuntimeException $e) {
            $this->error("❌ " . $e->getMessage());
            return CommandAlias::FAILURE;
        }


    }
}
