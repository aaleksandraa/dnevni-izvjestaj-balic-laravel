<?php

namespace Database\Factories;

use App\Models\DailyReport;
use App\Models\DailyReportFindingItem;
use App\Models\Finding;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyReportFindingItem>
 */
class DailyReportFindingItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 5, 150);

        return [
            'daily_report_id' => DailyReport::factory(),
            'finding_id' => Finding::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => round($quantity * $unitPrice, 2),
            'notes' => fake()->optional()->sentence(),
            'entered_by_user_id' => User::factory(),
        ];
    }
}
