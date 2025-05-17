<?php

namespace Modules\Core\Tests\Core\Models;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Core\Database\Factories\ModuleFactory;
use Modules\Core\Models\Module;
use Tests\TestCase;

class ModuleTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_creates_an_module(): void
    {
        $module = ModuleFactory::new()->create();
        $this->assertInstanceOf(Module::class, $module);
    }
}
