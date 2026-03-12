<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_category_id' => ServiceCategory::factory(),
            'name' => fake()->unique()->words(3, true),
            'base_price' => fake()->randomFloat(2, 10, 500),
            'is_active' => true,
            'code' => strtoupper(fake()->bothify('USL-###??')),
            'description' => fake()->optional()->sentence(),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
