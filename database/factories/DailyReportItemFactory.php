<?php

namespace Database\Factories;

use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\Patient;
use App\Models\Service;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyReportItem>
 */
class DailyReportItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $itemPrice = fake()->randomFloat(2, 20, 300);
        $paidAmount = fake()->randomFloat(2, 0, $itemPrice);
        $remainingAmount = round($itemPrice - $paidAmount, 2);

        $paymentStatus = 'neplaceno';
        if ($paidAmount == 0.0) {
            $paymentStatus = 'neplaceno';
        } elseif ($remainingAmount == 0.0) {
            $paymentStatus = 'placeno';
        } else {
            $paymentStatus = 'djelimicno_placeno';
        }

        return [
            'daily_report_id' => DailyReport::factory(),
            'patient_id' => Patient::factory(),
            'patient_full_name' => fake()->name(),
            'is_new_patient' => false,
            'service_id' => Service::factory(),
            'doctor_id' => StaffMember::factory(),
            'item_price' => $itemPrice,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentStatus === 'neplaceno'
                ? null
                : fake()->randomElement(['fiskalno', 'nefiskalno', 'karticno', 'ziralno']),
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'unpaid_reason' => $paymentStatus === 'neplaceno' ? 'Nije placeno danas' : null,
            'notes' => fake()->optional()->sentence(),
            'entered_by_user_id' => User::factory(),
        ];
    }
}
