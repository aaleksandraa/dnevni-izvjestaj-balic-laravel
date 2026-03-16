<?php

namespace Database\Factories;

use App\Models\DailyReport;
use App\Models\DailyReportFindingItem;
use App\Models\Finding;
use App\Models\Patient;
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
        $totalPrice = round($quantity * $unitPrice, 2);
        $paymentStatus = fake()->randomElement(['placeno', 'neplaceno', 'djelimicno_placeno']);
        $paidAmount = match ($paymentStatus) {
            'placeno' => $totalPrice,
            'djelimicno_placeno' => round($totalPrice / 2, 2),
            default => 0,
        };
        $remainingAmount = round($totalPrice - $paidAmount, 2);

        return [
            'daily_report_id' => DailyReport::factory(),
            'finding_id' => Finding::factory(),
            'patient_id' => fake()->boolean(40) ? Patient::factory() : null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentStatus === 'neplaceno' ? null : fake()->randomElement(['fiskalno', 'karticno', 'ziralno', 'nefiskalno']),
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'unpaid_reason' => $paymentStatus === 'neplaceno'
                ? 'Nije placeno danas'
                : ($paymentStatus === 'djelimicno_placeno' ? 'Djelimicno placeno' : null),
            'notes' => fake()->optional()->sentence(),
            'entered_by_user_id' => User::factory(),
        ];
    }
}
