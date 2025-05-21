<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Core\Services\Makes\MakeLivewire;

class MakeLivewireCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lawoo:make:livewire {name} {module} {--r|resources : Create a view for the Livewire Component}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Livewire Component for the specified module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $module = Str::studly($this->argument('module'));
        $res = Str::studly($this->option('resources'));

        $result = MakeLivewire::run($name, $module, $res);
        $this->components->{$result['type']}($result['messages']);
    }
}
