<?php

namespace Database\Seeders;

use App\Models\Finding;
use App\Models\FindingCategory;
use App\Models\Service;
use Illuminate\Database\Seeder;

class FindingCatalogSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Hormonski panel', 'sort_order' => 1],
            ['name' => 'Mikrobiologija', 'sort_order' => 2],
            ['name' => 'Genetski testovi', 'sort_order' => 3],
            ['name' => 'Opsti laboratorijski', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            FindingCategory::query()->updateOrCreate(
                ['name' => $category['name']],
                [
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );
        }

        $categoryIds = FindingCategory::query()->pluck('id', 'name');
        $serviceIds = Service::query()->pluck('id', 'code');

        $findings = [
            [
                'category' => 'Hormonski panel',
                'name' => 'AMH',
                'unit_price' => 75.00,
                'service_code' => 'LAB-001',
                'notes' => 'Najcesce trazen nalaz.',
            ],
            [
                'category' => 'Hormonski panel',
                'name' => 'FSH',
                'unit_price' => 35.00,
                'service_code' => 'LAB-001',
                'notes' => null,
            ],
            [
                'category' => 'Hormonski panel',
                'name' => 'LH',
                'unit_price' => 35.00,
                'service_code' => 'LAB-001',
                'notes' => null,
            ],
            [
                'category' => 'Hormonski panel',
                'name' => 'Estradiol',
                'unit_price' => 40.00,
                'service_code' => 'LAB-001',
                'notes' => null,
            ],
            [
                'category' => 'Opsti laboratorijski',
                'name' => 'Beta hCG kvantitativni',
                'unit_price' => 35.00,
                'service_code' => 'LAB-002',
                'notes' => null,
            ],
            [
                'category' => 'Mikrobiologija',
                'name' => 'Ureaplazma PCR',
                'unit_price' => 55.00,
                'service_code' => null,
                'notes' => 'PCR metodologija.',
            ],
            [
                'category' => 'Mikrobiologija',
                'name' => 'Klamidija PCR',
                'unit_price' => 55.00,
                'service_code' => null,
                'notes' => 'PCR metodologija.',
            ],
            [
                'category' => 'Genetski testovi',
                'name' => 'Kariotip parova',
                'unit_price' => 280.00,
                'service_code' => 'PRC-002',
                'notes' => 'Genetska obrada para prije IVF ciklusa.',
            ],
            [
                'category' => 'Genetski testovi',
                'name' => 'Trombofilija panel',
                'unit_price' => 210.00,
                'service_code' => null,
                'notes' => 'Prosireni panel mutacija.',
            ],
        ];

        foreach ($findings as $finding) {
            $categoryId = $categoryIds[$finding['category']] ?? null;
            if (! $categoryId) {
                continue;
            }

            Finding::query()->updateOrCreate(
                [
                    'finding_category_id' => $categoryId,
                    'name' => $finding['name'],
                ],
                [
                    'service_id' => $finding['service_code']
                        ? ($serviceIds[$finding['service_code']] ?? null)
                        : null,
                    'unit_price' => $finding['unit_price'],
                    'is_active' => true,
                    'notes' => $finding['notes'],
                ]
            );
        }
    }
}
