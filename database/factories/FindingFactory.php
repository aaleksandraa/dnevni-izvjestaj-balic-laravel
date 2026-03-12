<?php

namespace Database\Factories;

use App\Models\FindingCategory;
use App\Models\Service;
use App\Models\Finding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Finding>
 */
class FindingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'finding_category_id' => FindingCategory::factory(),
            'service_id' => Service::factory(),
            'name' => fake()->unique()->words(3, true),
            'unit_price' => fake()->optional(0.7)->randomFloat(2, 5, 200),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
