<?php

namespace Database\Factories;

use App\Models\StaffMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffMember>
 */
class StaffMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'role_type' => fake()->randomElement([
                'primarni_doktor',
                'sekundarni_doktor',
                'saradnik',
                'osoblje',
            ]),
            'title' => fake()->optional()->title(),
            'specialty' => fake()->optional()->word(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'internal_code' => strtoupper(fake()->unique()->bothify('SM-###??')),
            'is_active' => true,
        ];
    }
}
