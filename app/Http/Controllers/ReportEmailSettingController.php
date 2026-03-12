<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportEmailSettingRequest;
use App\Http\Requests\UpdateReportEmailSettingRequest;
use App\Models\ReportEmailSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportEmailSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $reportType = trim((string) $request->input('report_type', ''));
        $status = trim((string) $request->input('status', ''));

        $settings = ReportEmailSetting::query()
            ->when($search !== '', fn ($query) => $query->where('email', 'like', "%{$search}%"))
            ->when($reportType !== '', fn ($query) => $query->where('report_type', $reportType))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('report_type')
            ->orderBy('email')
            ->paginate(15)
            ->withQueryString();

        return view('report-email-settings.index', [
            'settings' => $settings,
            'reportTypes' => $this->reportTypeOptions(),
            'search' => $search,
            'reportType' => $reportType,
            'status' => $status,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('report-email-settings.create', [
            'reportTypes' => $this->reportTypeOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReportEmailSettingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['report_type'] = trim((string) $validated['report_type']);
        $validated['email'] = strtolower(trim((string) $validated['email']));
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        ReportEmailSetting::query()->create($validated);

        return redirect()
            ->route('report-email-settings.index')
            ->with('status', 'Primaoc email izvjestaja je uspjesno dodan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ReportEmailSetting $reportEmailSetting): View
    {
        return view('report-email-settings.show', [
            'setting' => $reportEmailSetting,
            'reportTypes' => $this->reportTypeOptions(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReportEmailSetting $reportEmailSetting): View
    {
        return view('report-email-settings.edit', [
            'setting' => $reportEmailSetting,
            'reportTypes' => $this->reportTypeOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReportEmailSettingRequest $request, ReportEmailSetting $reportEmailSetting): RedirectResponse
    {
        $validated = $request->validated();
        $validated['report_type'] = trim((string) $validated['report_type']);
        $validated['email'] = strtolower(trim((string) $validated['email']));
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $reportEmailSetting->update($validated);

        return redirect()
            ->route('report-email-settings.index')
            ->with('status', 'Primaoc email izvjestaja je uspjesno azuriran.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReportEmailSetting $reportEmailSetting): RedirectResponse
    {
        if ($reportEmailSetting->is_active) {
            $reportEmailSetting->update([
                'is_active' => false,
            ]);
        }

        return redirect()
            ->route('report-email-settings.index')
            ->with('status', 'Primaoc email izvjestaja je deaktiviran.');
    }

    /**
     * @return array<string, string>
     */
    private function reportTypeOptions(): array
    {
        return [
            'daily' => 'Dnevni',
            'weekly' => 'Sedmicni',
            'monthly' => 'Mjesecni',
        ];
    }
}
