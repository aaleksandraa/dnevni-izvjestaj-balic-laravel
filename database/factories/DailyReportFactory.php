<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use App\Models\DailyReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyReport>
 */
class DailyReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_date' => fake()->dateTimeBetween('-20 days', 'now')->format('Y-m-d'),
            'location_id' => Location::factory(),
            'created_by_user_id' => User::factory(),
            'submitted_by_user_id' => null,
            'last_edited_by_user_id' => null,
            'status' => 'u_radu',
            'notes' => fake()->optional()->sentence(),
            'submitted_at' => null,
            'locked_at' => null,
        ];
    }
}
