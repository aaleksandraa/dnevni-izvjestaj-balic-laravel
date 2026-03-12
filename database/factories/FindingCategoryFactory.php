<?php

namespace Database\Factories;

use App\Models\FindingCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FindingCategory>
 */
class FindingCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
