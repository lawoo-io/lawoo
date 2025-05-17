<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\ModuleCategory;

class ModuleCategoryFactory extends Factory
{
    protected $model = ModuleCategory::class;

    public function definition(): array {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->slug(),
        ];
    }
}
