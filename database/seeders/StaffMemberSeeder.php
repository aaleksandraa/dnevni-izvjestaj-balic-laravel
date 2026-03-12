<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\StaffMember;
use Illuminate\Database\Seeder;

class StaffMemberSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $staffMembers = [
            [
                'full_name' => 'Dr Lejla Hasanovic',
                'role_type' => 'primarni_doktor',
                'title' => 'Spec. ginekologije i opstetricije',
                'specialty' => 'Reproduktivna medicina',
                'email' => 'lejla.hasanovic@reports-ivf.local',
                'phone' => '+387 61 111 001',
                'internal_code' => 'DR-001',
                'location_names' => ['IVF Centar Sarajevo', 'IVF Centar Tuzla'],
            ],
            [
                'full_name' => 'Dr Amar Kovacevic',
                'role_type' => 'primarni_doktor',
                'title' => 'Spec. ginekologije i opstetricije',
                'specialty' => 'IVF protokoli',
                'email' => 'amar.kovacevic@reports-ivf.local',
                'phone' => '+387 61 111 002',
                'internal_code' => 'DR-002',
                'location_names' => ['IVF Centar Sarajevo'],
            ],
            [
                'full_name' => 'Dr Nina Petrovic',
                'role_type' => 'sekundarni_doktor',
                'title' => 'Spec. radiologije',
                'specialty' => 'Ultrazvucna dijagnostika',
                'email' => 'nina.petrovic@reports-ivf.local',
                'phone' => '+387 61 111 003',
                'internal_code' => 'DR-003',
                'location_names' => ['IVF Centar Tuzla', 'IVF Centar Banja Luka'],
            ],
            [
                'full_name' => 'Dr Marko Ilic',
                'role_type' => 'sekundarni_doktor',
                'title' => 'Spec. interne medicine',
                'specialty' => 'Hormonska terapija',
                'email' => 'marko.ilic@reports-ivf.local',
                'phone' => '+387 61 111 004',
                'internal_code' => 'DR-004',
                'location_names' => ['IVF Centar Sarajevo', 'IVF Centar Banja Luka'],
            ],
            [
                'full_name' => 'Dr Amina Selimovic',
                'role_type' => 'saradnik',
                'title' => 'Dr sci biologije',
                'specialty' => 'Embriologija',
                'email' => 'amina.selimovic@reports-ivf.local',
                'phone' => '+387 61 111 005',
                'internal_code' => 'SR-001',
                'location_names' => ['IVF Centar Sarajevo'],
            ],
            [
                'full_name' => 'Dr Ivan Radic',
                'role_type' => 'saradnik',
                'title' => 'Spec. laboratorijske medicine',
                'specialty' => 'Andrologija',
                'email' => 'ivan.radic@reports-ivf.local',
                'phone' => '+387 61 111 006',
                'internal_code' => 'SR-002',
                'location_names' => ['IVF Centar Tuzla'],
            ],
            [
                'full_name' => 'Mina Halilovic',
                'role_type' => 'osoblje',
                'title' => 'Med. sestra',
                'specialty' => 'Operativna podrska',
                'email' => 'mina.halilovic@reports-ivf.local',
                'phone' => '+387 61 111 007',
                'internal_code' => 'OS-001',
                'location_names' => ['IVF Centar Sarajevo', 'IVF Centar Tuzla', 'IVF Centar Banja Luka'],
            ],
        ];

        $locationIds = Location::query()
            ->pluck('id', 'name');

        foreach ($staffMembers as $row) {
            $member = StaffMember::query()->updateOrCreate(
                ['internal_code' => $row['internal_code']],
                [
                    'full_name' => $row['full_name'],
                    'role_type' => $row['role_type'],
                    'title' => $row['title'],
                    'specialty' => $row['specialty'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'is_active' => true,
                ]
            );

            $assignedLocationIds = collect($row['location_names'])
                ->map(fn (string $name) => $locationIds[$name] ?? null)
                ->filter()
                ->map(fn ($id): int => (int) $id)
                ->values()
                ->all();

            $member->locations()->sync($assignedLocationIds);
        }
    }
}
