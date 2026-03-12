<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $patients = [
            [
                'full_name' => 'Aida Kovacevic',
                'date_of_birth' => '1991-04-12',
                'phone' => '+387 61 700 101',
                'email' => 'aida.kovacevic@example.com',
                'notes' => null,
                'is_active' => true,
            ],
            [
                'full_name' => 'Mirela Hasanbegovic',
                'date_of_birth' => '1987-09-25',
                'phone' => '+387 61 700 102',
                'email' => 'mirela.hasanbegovic@example.com',
                'notes' => 'Alergija na penicilin.',
                'is_active' => true,
            ],
            [
                'full_name' => 'Jasmina Radic',
                'date_of_birth' => '1994-01-03',
                'phone' => '+387 61 700 103',
                'email' => 'jasmina.radic@example.com',
                'notes' => null,
                'is_active' => true,
            ],
            [
                'full_name' => 'Ivana Vukovic',
                'date_of_birth' => '1989-06-17',
                'phone' => '+387 61 700 104',
                'email' => 'ivana.vukovic@example.com',
                'notes' => 'Prethodni IVF ciklus 2024.',
                'is_active' => true,
            ],
        ];

        foreach ($patients as $patient) {
            Patient::query()->updateOrCreate(
                ['full_name' => $patient['full_name'], 'date_of_birth' => $patient['date_of_birth']],
                $patient
            );
        }
    }
}
