<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\DailyReportItem;
use App\Models\Location;
use App\Models\Patient;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));
        $locationId = (int) $request->input('location_id', 0);
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));

        $patientsQuery = Patient::query()
            ->when($search !== '', fn (Builder $query) => $query->where('full_name', 'like', "%{$search}%"))
            ->when($status === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn (Builder $query) => $query->where('is_active', false));

        $applyItemFilters = function (Builder $query) use ($locationId, $dateFrom, $dateTo): void {
            $query->whereHas('dailyReport', function (Builder $dailyReportQuery) use ($locationId, $dateFrom, $dateTo): void {
                if ($locationId > 0) {
                    $dailyReportQuery->where('location_id', $locationId);
                }

                if ($dateFrom !== '') {
                    $dailyReportQuery->whereDate('report_date', '>=', $dateFrom);
                }

                if ($dateTo !== '') {
                    $dailyReportQuery->whereDate('report_date', '<=', $dateTo);
                }
            });
        };

        if ($locationId > 0 || $dateFrom !== '' || $dateTo !== '') {
            $patientsQuery->whereHas('dailyReportItems', $applyItemFilters);
        }

        $patients = $patientsQuery
            ->withCount(['dailyReportItems as exams_count' => $applyItemFilters])
            ->withSum(['dailyReportItems as total_amount' => $applyItemFilters], 'item_price')
            ->withSum(['dailyReportItems as paid_amount' => $applyItemFilters], 'paid_amount')
            ->withSum(['dailyReportItems as remaining_amount' => $applyItemFilters], 'remaining_amount')
            ->orderBy('full_name')
            ->paginate(20)
            ->withQueryString();

        $locations = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('patients.index', [
            'patients' => $patients,
            'locations' => $locations,
            'search' => $search,
            'status' => $status,
            'locationId' => $locationId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function create(): View
    {
        return view('patients.create');
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['full_name'] = trim((string) $validated['full_name']);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['phone'] = $validated['phone'] ?? null;
        $validated['email'] = $validated['email'] ?? null;
        $validated['notes'] = $validated['notes'] ?? null;

        Patient::query()->create($validated);

        return redirect()
            ->route('patients.index')
            ->with('status', 'Pacijent je uspjesno kreiran.');
    }

    public function show(Request $request, Patient $patient): View
    {
        $locationId = (int) $request->input('location_id', 0);
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));

        $baseQuery = DailyReportItem::query()
            ->where('patient_id', $patient->id)
            ->whereHas('dailyReport', function (Builder $dailyReportQuery) use ($locationId, $dateFrom, $dateTo): void {
                if ($locationId > 0) {
                    $dailyReportQuery->where('location_id', $locationId);
                }

                if ($dateFrom !== '') {
                    $dailyReportQuery->whereDate('report_date', '>=', $dateFrom);
                }

                if ($dateTo !== '') {
                    $dailyReportQuery->whereDate('report_date', '<=', $dateTo);
                }
            });

        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as exams_count,
                COALESCE(SUM(item_price), 0) as total_amount,
                COALESCE(SUM(paid_amount), 0) as paid_amount,
                COALESCE(SUM(remaining_amount), 0) as remaining_amount
            ')
            ->first();

        $paymentByMethod = (clone $baseQuery)
            ->whereIn('payment_status', ['placeno', 'djelimicno_placeno'])
            ->selectRaw("
                COALESCE(NULLIF(payment_method, ''), 'nepoznato') as method_key,
                COUNT(*) as items_count,
                COALESCE(SUM(paid_amount), 0) as paid_amount
            ")
            ->groupByRaw("COALESCE(NULLIF(payment_method, ''), 'nepoznato')")
            ->orderByDesc('paid_amount')
            ->get()
            ->map(function ($row): array {
                $labels = [
                    'fiskalno' => 'Fiskalno',
                    'nefiskalno' => 'Nefiskalno',
                    'karticno' => 'Karticno',
                    'ziralno' => 'Ziralno',
                    'nepoznato' => 'Nepoznato',
                ];

                $key = (string) $row->method_key;

                return [
                    'method' => $labels[$key] ?? ucfirst($key),
                    'items_count' => (int) $row->items_count,
                    'paid_amount' => round((float) $row->paid_amount, 2),
                ];
            });

        $fullyUnpaid = (clone $baseQuery)
            ->where('payment_status', 'neplaceno')
            ->selectRaw('COUNT(*) as items_count, COALESCE(SUM(remaining_amount), 0) as amount')
            ->first();

        $partiallyPaid = (clone $baseQuery)
            ->where('payment_status', 'djelimicno_placeno')
            ->selectRaw('COUNT(*) as items_count, COALESCE(SUM(remaining_amount), 0) as amount')
            ->first();

        $items = (clone $baseQuery)
            ->with(['dailyReport.location', 'service', 'doctor'])
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $locations = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('patients.show', [
            'patient' => $patient,
            'summary' => [
                'exams_count' => (int) ($summary?->exams_count ?? 0),
                'total_amount' => round((float) ($summary?->total_amount ?? 0), 2),
                'paid_amount' => round((float) ($summary?->paid_amount ?? 0), 2),
                'remaining_amount' => round((float) ($summary?->remaining_amount ?? 0), 2),
                'fully_unpaid_count' => (int) ($fullyUnpaid?->items_count ?? 0),
                'fully_unpaid_amount' => round((float) ($fullyUnpaid?->amount ?? 0), 2),
                'partially_paid_count' => (int) ($partiallyPaid?->items_count ?? 0),
                'partially_paid_amount' => round((float) ($partiallyPaid?->amount ?? 0), 2),
            ],
            'paymentByMethod' => $paymentByMethod,
            'items' => $items,
            'locations' => $locations,
            'locationId' => $locationId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function edit(Patient $patient): View
    {
        return view('patients.edit', [
            'patient' => $patient,
        ]);
    }

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validated();
        $validated['full_name'] = trim((string) $validated['full_name']);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['phone'] = $validated['phone'] ?? null;
        $validated['email'] = $validated['email'] ?? null;
        $validated['notes'] = $validated['notes'] ?? null;

        $patient->update($validated);

        return redirect()
            ->route('patients.show', $patient)
            ->with('status', 'Pacijent je uspjesno azuriran.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        if ($patient->is_active) {
            $patient->update([
                'is_active' => false,
            ]);
        }

        return redirect()
            ->route('patients.index')
            ->with('status', 'Pacijent je deaktiviran.');
    }
}
