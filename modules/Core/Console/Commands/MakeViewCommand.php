<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Core\Services\Makes\MakeView;

class MakeViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lawoo:make:view {name} {module} {--c|component : Add component path to the file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new View file for the specified module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $module = Str::studly($this->argument('module'));
        $component = Str::studly($this->option('component'));

        $result = MakeView::run($name, $module, $component);
        $this->components->{$result['type']}($result['messages']);
    }
}
