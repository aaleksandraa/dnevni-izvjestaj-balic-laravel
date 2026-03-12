<?php

namespace Database\Factories;

use App\Models\ReportEmailSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportEmailSetting>
 */
class ReportEmailSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_type' => fake()->randomElement(ReportEmailSetting::REPORT_TYPES),
            'email' => fake()->unique()->safeEmail(),
            'is_active' => true,
        ];
    }
}
