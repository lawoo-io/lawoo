<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Module;
use Modules\Core\Models\ModuleCategory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    protected $model = Module::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'system_name' => fake()->slug,
            'module_category_id' => ModuleCategory::factory()->create(),
            'short_desc' => fake()->text(50),
            'author' => fake()->name,
            'author_url' => fake()->url,
            'version' => '1.0.1-beta'
        ];
    }
}
