<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDailyEmailSummaryConfigurationRequest;
use App\Models\Service;
use App\Models\StaffMember;
use App\Services\DailyEmailSummaryConfigurationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DailyEmailSummaryConfigurationController extends Controller
{
    public function edit(DailyEmailSummaryConfigurationService $configurationService): View
    {
        $configuration = $configurationService->get();

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $collaborators = StaffMember::query()
            ->where('is_active', true)
            ->where('role_type', 'saradnik')
            ->orderBy('full_name')
            ->get();

        $leadDoctors = StaffMember::query()
            ->where('is_active', true)
            ->whereIn('role_type', ['primarni_doktor', 'sekundarni_doktor'])
            ->orderBy('full_name')
            ->get();

        return view('settings.daily-email-summary', [
            'configuration' => $configuration,
            'services' => $services,
            'collaborators' => $collaborators,
            'leadDoctors' => $leadDoctors,
        ]);
    }

    public function update(
        UpdateDailyEmailSummaryConfigurationRequest $request,
        DailyEmailSummaryConfigurationService $configurationService
    ): RedirectResponse {
        $configurationService->save($request->validated());

        return redirect()
            ->route('settings.daily-email-summary.edit')
            ->with('status', 'Podesavanja dnevnog email izvjestaja su uspjesno sacuvana.');
    }
}
