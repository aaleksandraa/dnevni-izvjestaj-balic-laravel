<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Ginekoloski pregledi', 'sort_order' => 1],
            ['name' => 'IVF konsultacije', 'sort_order' => 2],
            ['name' => 'Ultrazvuk', 'sort_order' => 3],
            ['name' => 'Laboratorija', 'sort_order' => 4],
            ['name' => 'Proceduralne usluge', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            ServiceCategory::query()->updateOrCreate(
                ['name' => $category['name']],
                [
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );
        }

        $categoryIds = ServiceCategory::query()
            ->pluck('id', 'name');

        $services = [
            [
                'category' => 'Ginekoloski pregledi',
                'name' => 'Prvi ginekoloski pregled',
                'base_price' => 80.00,
                'code' => 'GIN-001',
                'description' => 'Inicijalni pregled sa anamnezom.',
                'sort_order' => 1,
            ],
            [
                'category' => 'Ginekoloski pregledi',
                'name' => 'Kontrolni ginekoloski pregled',
                'base_price' => 60.00,
                'code' => 'GIN-002',
                'description' => 'Kontrolni pregled i pracenje terapije.',
                'sort_order' => 2,
            ],
            [
                'category' => 'IVF konsultacije',
                'name' => 'IVF konsultacija inicijalna',
                'base_price' => 120.00,
                'code' => 'IVF-001',
                'description' => 'Prva IVF konsultacija i plan tretmana.',
                'sort_order' => 1,
            ],
            [
                'category' => 'IVF konsultacije',
                'name' => 'IVF konsultacija kontrola',
                'base_price' => 90.00,
                'code' => 'IVF-002',
                'description' => 'Kontrolna IVF konsultacija.',
                'sort_order' => 2,
            ],
            [
                'category' => 'Ultrazvuk',
                'name' => 'Transvaginalni ultrazvuk',
                'base_price' => 70.00,
                'code' => 'ULZ-001',
                'description' => 'Standardni transvaginalni UZ pregled.',
                'sort_order' => 1,
            ],
            [
                'category' => 'Ultrazvuk',
                'name' => 'Folikulometrija',
                'base_price' => 65.00,
                'code' => 'ULZ-002',
                'description' => 'Pracenje folikularnog rasta.',
                'sort_order' => 2,
            ],
            [
                'category' => 'Laboratorija',
                'name' => 'AMH analiza',
                'base_price' => 75.00,
                'code' => 'LAB-001',
                'description' => 'Anti-Mullerov hormon.',
                'sort_order' => 1,
            ],
            [
                'category' => 'Laboratorija',
                'name' => 'Beta hCG',
                'base_price' => 35.00,
                'code' => 'LAB-002',
                'description' => 'Kvantitativna beta hCG analiza.',
                'sort_order' => 2,
            ],
            [
                'category' => 'Proceduralne usluge',
                'name' => 'IUI procedura',
                'base_price' => 220.00,
                'code' => 'PRC-001',
                'description' => 'Intrauterina inseminacija.',
                'sort_order' => 1,
            ],
            [
                'category' => 'Proceduralne usluge',
                'name' => 'Embriotransfer',
                'base_price' => 350.00,
                'code' => 'PRC-002',
                'description' => 'Transfer embriona.',
                'sort_order' => 2,
            ],
        ];

        foreach ($services as $service) {
            $categoryId = $categoryIds[$service['category']] ?? null;

            if (! $categoryId) {
                continue;
            }

            Service::query()->updateOrCreate(
                ['code' => $service['code']],
                [
                    'service_category_id' => $categoryId,
                    'name' => $service['name'],
                    'base_price' => $service['base_price'],
                    'is_active' => true,
                    'description' => $service['description'],
                    'sort_order' => $service['sort_order'],
                ]
            );
        }
    }
}
