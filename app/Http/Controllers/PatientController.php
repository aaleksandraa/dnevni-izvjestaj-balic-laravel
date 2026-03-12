<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientPaymentRequest;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\Location;
use App\Models\Patient;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\StaffMember;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));
        $locationId = (int) $request->input('location_id', 0);
        $dateFromInput = trim((string) $request->input('date_from', ''));
        $dateToInput = trim((string) $request->input('date_to', ''));
        $dateFrom = $this->parseDateInput($dateFromInput);
        $dateTo = $this->parseDateInput($dateToInput);

        $patientsQuery = Patient::query()
            ->when($search !== '', fn (Builder $query) => $query->where('full_name', 'like', "%{$search}%"))
            ->when($status === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn (Builder $query) => $query->where('is_active', false));

        $applyItemFilters = function (Builder $query) use ($locationId, $dateFrom, $dateTo): void {
            $query->whereHas('dailyReport', function (Builder $dailyReportQuery) use ($locationId, $dateFrom, $dateTo): void {
                if ($locationId > 0) {
                    $dailyReportQuery->where('location_id', $locationId);
                }

                if ($dateFrom !== null) {
                    $dailyReportQuery->whereDate('report_date', '>=', $dateFrom);
                }

                if ($dateTo !== null) {
                    $dailyReportQuery->whereDate('report_date', '<=', $dateTo);
                }
            });
        };

        if ($locationId > 0 || $dateFrom !== null || $dateTo !== null) {
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
            'dateFrom' => $this->formatDateForDisplay($dateFrom, $dateFromInput),
            'dateTo' => $this->formatDateForDisplay($dateTo, $dateToInput),
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
        $dateFromInput = trim((string) $request->input('date_from', ''));
        $dateToInput = trim((string) $request->input('date_to', ''));
        $dateFrom = $this->parseDateInput($dateFromInput);
        $dateTo = $this->parseDateInput($dateToInput);

        $baseQuery = DailyReportItem::query()
            ->where('patient_id', $patient->id)
            ->whereHas('dailyReport', function (Builder $dailyReportQuery) use ($locationId, $dateFrom, $dateTo): void {
                if ($locationId > 0) {
                    $dailyReportQuery->where('location_id', $locationId);
                }

                if ($dateFrom !== null) {
                    $dailyReportQuery->whereDate('report_date', '>=', $dateFrom);
                }

                if ($dateTo !== null) {
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
            ->selectRaw('payment_method, COUNT(*) as items_count, COALESCE(SUM(paid_amount), 0) as paid_amount')
            ->groupBy('payment_method')
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

                $method = trim((string) ($row->payment_method ?? ''));
                $key = $method !== '' ? $method : 'nepoznato';

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
        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $doctors = StaffMember::query()
            ->where('is_active', true)
            ->whereIn('role_type', ['primarni_doktor', 'sekundarni_doktor', 'saradnik'])
            ->orderBy('full_name')
            ->get();
        $paymentMethods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $todayDateDisplay = now()->format('d.m.Y');

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
            'services' => $services,
            'doctors' => $doctors,
            'paymentMethods' => $paymentMethods,
            'todayDateDisplay' => $todayDateDisplay,
            'locationId' => $locationId,
            'dateFrom' => $this->formatDateForDisplay($dateFrom, $dateFromInput),
            'dateTo' => $this->formatDateForDisplay($dateTo, $dateToInput),
        ]);
    }

    public function storePayment(
        StorePatientPaymentRequest $request,
        Patient $patient,
        AuditLogService $auditLogService
    ): RedirectResponse {
        if (! $patient->is_active) {
            throw ValidationException::withMessages([
                'patient' => 'Placanje nije moguce dodati za neaktivnog pacijenta.',
            ]);
        }

        $validated = $request->validated();
        $reportDate = $this->parseDateInput((string) $validated['report_date']);
        $today = now()->toDateString();

        if ($reportDate === null || $reportDate !== $today) {
            throw ValidationException::withMessages([
                'report_date' => 'Placanje iz kartona je dozvoljeno samo za danasnji datum.',
            ]);
        }

        $locationId = (int) $validated['location_id'];
        $doctorId = isset($validated['doctor_id']) && (int) $validated['doctor_id'] > 0
            ? (int) $validated['doctor_id']
            : null;

        if ($doctorId !== null) {
            $doctorExistsOnLocation = StaffMember::query()
                ->whereKey($doctorId)
                ->where('is_active', true)
                ->whereHas('locations', fn (Builder $query) => $query->where('locations.id', $locationId))
                ->exists();

            if (! $doctorExistsOnLocation) {
                throw ValidationException::withMessages([
                    'doctor_id' => 'Odabrani doktor nije aktivan za ovu lokaciju.',
                ]);
            }
        }

        [$paymentStatus, $paidAmount, $remainingAmount, $paymentMethod, $unpaidReason] = $this->normalizePaymentInput($validated);

        $actor = $request->user();

        $dailyReport = DailyReport::query()
            ->whereDate('report_date', $today)
            ->where('location_id', $locationId)
            ->first();

        if ($dailyReport === null) {
            $dailyReport = DailyReport::query()->create([
                'report_date' => $today,
                'location_id' => $locationId,
                'status' => 'u_radu',
                'notes' => null,
                'created_by_user_id' => $actor?->id,
                'last_edited_by_user_id' => $actor?->id,
            ]);
        }

        if ($dailyReport->status !== 'u_radu' || $dailyReport->locked_at !== null) {
            throw ValidationException::withMessages([
                'location_id' => 'Danasnji izvjestaj za odabranu lokaciju nije u statusu u_radu.',
            ]);
        }

        $item = DailyReportItem::query()->create([
            'daily_report_id' => $dailyReport->id,
            'patient_id' => $patient->id,
            'patient_full_name' => $patient->full_name,
            'is_new_patient' => (bool) ($validated['is_new_patient'] ?? false),
            'service_id' => (int) $validated['service_id'],
            'doctor_id' => $doctorId,
            'item_price' => (float) $validated['item_price'],
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'unpaid_reason' => $unpaidReason,
            'notes' => $validated['notes'] ?? null,
            'entered_by_user_id' => $actor?->id,
        ]);

        $dailyReport->update([
            'last_edited_by_user_id' => $actor?->id,
        ]);

        $auditLogService->log(
            $actor,
            'daily_report_items',
            $item->id,
            'created',
            null,
            [
                'report' => $this->reportItemAuditContext($dailyReport),
                'item' => $this->serviceItemAuditPayload($item),
            ],
            'Dodana stavka usluge iz kartona pacijenta #'.$patient->id.' u izvjestaj #'.$dailyReport->id
        );

        return redirect()
            ->route('patients.show', $patient)
            ->with('status', 'Placanje je uspjesno dodano u danasnji izvjestaj.');
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

    /**
     * @param array<string, mixed> $validated
     * @return array{string, float, float, string|null, string|null}
     */
    private function normalizePaymentInput(array $validated): array
    {
        $itemPrice = (float) ($validated['item_price'] ?? 0);
        $paymentStatus = (string) ($validated['payment_status'] ?? 'neplaceno');
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);
        $paymentMethod = $validated['payment_method'] ?? null;
        $unpaidReason = $validated['unpaid_reason'] ?? null;

        if ($paymentStatus === 'neplaceno') {
            if (trim((string) $unpaidReason) === '') {
                throw ValidationException::withMessages([
                    'unpaid_reason' => 'Razlog neplacanja je obavezan za neplacene stavke.',
                ]);
            }

            return ['neplaceno', 0, round($itemPrice, 2), null, trim((string) $unpaidReason)];
        }

        if ($paymentMethod === null || trim((string) $paymentMethod) === '') {
            throw ValidationException::withMessages([
                'payment_method' => 'Nacin placanja je obavezan za placene i djelimicno placene stavke.',
            ]);
        }

        if ($paidAmount > $itemPrice) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Placeni iznos ne moze biti veci od ukupne cijene stavke.',
            ]);
        }

        if ($paymentStatus === 'placeno') {
            if (round($paidAmount, 2) !== round($itemPrice, 2)) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Za status placeno, placeni iznos mora biti jednak cijeni stavke.',
                ]);
            }

            return ['placeno', round($paidAmount, 2), 0, trim((string) $paymentMethod), null];
        }

        if ($paymentStatus === 'djelimicno_placeno') {
            if ($paidAmount <= 0 || $paidAmount >= $itemPrice) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Za djelimicno placanje iznos mora biti veci od 0 i manji od pune cijene.',
                ]);
            }

            return [
                'djelimicno_placeno',
                round($paidAmount, 2),
                round($itemPrice - $paidAmount, 2),
                trim((string) $paymentMethod),
                trim((string) ($unpaidReason ?? 'Djelimicno placeno')),
            ];
        }

        throw ValidationException::withMessages([
            'payment_status' => 'Nepodrzan status placanja.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportItemAuditContext(DailyReport $dailyReport): array
    {
        return [
            'daily_report_id' => (int) $dailyReport->id,
            'report_date' => $dailyReport->report_date?->toDateString(),
            'location_id' => (int) $dailyReport->location_id,
            'status' => $dailyReport->status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceItemAuditPayload(DailyReportItem $item): array
    {
        return [
            'daily_report_id' => (int) $item->daily_report_id,
            'patient_id' => $item->patient_id !== null ? (int) $item->patient_id : null,
            'patient_full_name' => $item->patient?->full_name ?? $item->patient_full_name,
            'is_new_patient' => (bool) $item->is_new_patient,
            'service_id' => (int) $item->service_id,
            'doctor_id' => $item->doctor_id !== null ? (int) $item->doctor_id : null,
            'item_price' => (float) $item->item_price,
            'payment_status' => $item->payment_status,
            'payment_method' => $item->payment_method,
            'paid_amount' => (float) $item->paid_amount,
            'remaining_amount' => (float) $item->remaining_amount,
            'unpaid_reason' => $item->unpaid_reason,
            'notes' => $item->notes,
            'entered_by_user_id' => $item->entered_by_user_id !== null ? (int) $item->entered_by_user_id : null,
        ];
    }

    private function parseDateInput(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        foreach (['d.m.Y', 'd/m/Y', 'Y-m-d', 'm/d/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $trimmed);
            } catch (\Throwable) {
                $date = false;
            }

            if ($date !== false && $date->format($format) === $trimmed) {
                return $date->toDateString();
            }
        }

        return null;
    }

    private function formatDateForDisplay(?string $normalizedDate, string $originalInput): string
    {
        if ($normalizedDate !== null) {
            return Carbon::parse($normalizedDate)->format('d.m.Y');
        }

        return trim($originalInput);
    }
}
