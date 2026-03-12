<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $modules = [
            [
                'title' => 'Medicinski tim',
                'description' => 'Upravljanje doktorima, saradnicima i povezivanjem sa lokacijama.',
                'route' => route('staff-members.index'),
                'action' => 'Otvori tim',
            ],
            [
                'title' => 'Korisnici',
                'description' => 'Upravljanje sestrama/adminima i njihovim dozvolama pristupa.',
                'route' => route('users.index'),
                'action' => 'Otvori korisnike',
            ],
            [
                'title' => 'Email primaoci',
                'description' => 'Definisi kome se salju dnevni, sedmicni i mjesecni izvjestaji.',
                'route' => route('report-email-settings.index'),
                'action' => 'Otvori primaoce',
            ],
            [
                'title' => 'Dnevni email izvjestaj',
                'description' => 'Podesi koje usluge i osobe ulaze u automatski dnevni email rezime.',
                'route' => route('settings.daily-email-summary.edit'),
                'action' => 'Podesi sadrzaj emaila',
            ],
            [
                'title' => 'Audit log',
                'description' => 'Pregled svih izmjena korisnika i stavki izvjestaja kroz sistem.',
                'route' => route('audit-logs.index'),
                'action' => 'Otvori audit log',
            ],
        ];

        return view('settings.index', [
            'modules' => $modules,
        ]);
    }
}
