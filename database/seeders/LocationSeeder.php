<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'IVF Centar Sarajevo',
                'address' => 'Zmaja od Bosne 14',
                'city' => 'Sarajevo',
                'phone' => '+387 33 555 100',
                'email' => 'sarajevo@reports-ivf.local',
                'is_active' => true,
                'notes' => 'Glavna lokacija i administrativni centar.',
            ],
            [
                'name' => 'IVF Centar Tuzla',
                'address' => 'Slatina bb',
                'city' => 'Tuzla',
                'phone' => '+387 35 555 200',
                'email' => 'tuzla@reports-ivf.local',
                'is_active' => true,
                'notes' => 'Regionalna lokacija za konsultacije i laboratoriju.',
            ],
            [
                'name' => 'IVF Centar Banja Luka',
                'address' => 'Kralja Petra I 21',
                'city' => 'Banja Luka',
                'phone' => '+387 51 555 300',
                'email' => 'banjaluka@reports-ivf.local',
                'is_active' => true,
                'notes' => 'Lokacija fokusirana na dijagnostiku i kontrole.',
            ],
        ];

        foreach ($locations as $location) {
            Location::query()->updateOrCreate(
                ['name' => $location['name']],
                $location
            );
        }
    }
}
