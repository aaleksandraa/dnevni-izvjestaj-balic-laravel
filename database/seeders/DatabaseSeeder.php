<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\ReportEmailSetting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@reports-ivf.local'],
            [
                'name' => 'Glavni Admin',
                'password' => bcrypt('admin12345'),
                'role' => 'glavni_admin',
                'is_active' => true,
                'can_submit_report' => true,
                'can_change_submitter' => true,
            ]
        );

        $paymentMethods = [
            ['name' => 'Fiskalno', 'sort_order' => 1],
            ['name' => 'Nefiskalno', 'sort_order' => 2],
            ['name' => 'Karticno', 'sort_order' => 3],
            ['name' => 'Ziralno', 'sort_order' => 4],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['name' => $method['name']],
                [
                    'sort_order' => $method['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        foreach (['daily', 'weekly', 'monthly'] as $reportType) {
            ReportEmailSetting::query()->updateOrCreate(
                [
                    'report_type' => $reportType,
                    'email' => 'admin@reports-ivf.local',
                ],
                [
                    'is_active' => true,
                ]
            );
        }
    }
}
