<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Core\Services\Makes\MakeModel;

class MakeModelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lawoo:make:model {name} {module} {--s|schema : Create a schema for the model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model for the specified module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $module = Str::studly($this->argument('module'));
        $schema = $this->option('schema');

        $result = MakeModel::run($name, $module, $schema);
        $this->components->{$result['type']}($result['messages']);
    }
}
