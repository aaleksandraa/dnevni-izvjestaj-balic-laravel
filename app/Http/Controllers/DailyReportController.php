<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyReportFindingItemRequest;
use App\Http\Requests\StoreDailyReportItemRequest;
use App\Http\Requests\StoreDailyReportRequest;
use App\Http\Requests\UpdateDailyReportRequest;
use App\Jobs\SendDailyReportEmailJob;
use App\Models\DailyReport;
use App\Models\DailyReportFindingItem;
use App\Models\DailyReportItem;
use App\Models\Finding;
use App\Models\Location;
use App\Models\Patient;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\StaffMember;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class DailyReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));
        $status = trim((string) $request->input('status', ''));
        $locationId = (int) $request->input('location_id', 0);

        $reports = DailyReport::query()
            ->with(['location', 'createdBy', 'submittedBy'])
            ->withCount(['items', 'findingItems'])
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('report_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('report_date', '<=', $dateTo))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($locationId > 0, fn ($query) => $query->where('location_id', $locationId))
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $locations = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('daily-reports.index', [
            'reports' => $reports,
            'locations' => $locations,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'status' => $status,
            'locationId' => $locationId,
            'statusOptions' => ['u_radu', 'podnesen', 'zakljucan'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $locations = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('daily-reports.create', [
            'locations' => $locations,
            'defaultDate' => now()->toDateString(),
            'defaultDateDisplay' => now()->format('d.m.Y'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDailyReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $reportDate = now()->toDateString();

        $existingReport = DailyReport::query()
            ->whereDate('report_date', $reportDate)
            ->where('location_id', $validated['location_id'])
            ->first();

        if ($existingReport) {
            return redirect()
                ->route('daily-reports.show', $existingReport)
                ->with('status', 'Izvjestaj za odabrani datum i lokaciju vec postoji.');
        }

        $report = DailyReport::query()->create([
            'report_date' => $reportDate,
            'location_id' => $validated['location_id'],
            'status' => 'u_radu',
            'notes' => $validated['notes'] ?? null,
            'created_by_user_id' => $request->user()?->id,
            'last_edited_by_user_id' => $request->user()?->id,
        ]);

        return redirect()
            ->route('daily-reports.show', $report)
            ->with('status', 'Dnevni izvjestaj je uspjesno kreiran.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyReport $dailyReport): View
    {
        $dailyReport->load([
            'location',
            'createdBy',
            'submittedBy',
            'lastEditedBy',
            'items' => fn ($query) => $query
                ->with(['patient', 'service', 'doctor', 'enteredBy'])
                ->latest('id'),
            'findingItems' => fn ($query) => $query
                ->with(['finding', 'enteredBy'])
                ->latest('id'),
        ]);

        $locationId = $dailyReport->location_id;

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $doctors = StaffMember::query()
            ->where('is_active', true)
            ->whereIn('role_type', ['primarni_doktor', 'sekundarni_doktor', 'saradnik'])
            ->whereHas('locations', fn ($query) => $query->where('locations.id', $locationId))
            ->orderBy('full_name')
            ->get();

        $findings = Finding::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $paymentMethods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $patients = Patient::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        $possibleSubmitters = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('daily-reports.show', [
            'dailyReport' => $dailyReport,
            'services' => $services,
            'doctors' => $doctors,
            'findings' => $findings,
            'paymentMethods' => $paymentMethods,
            'patients' => $patients,
            'possibleSubmitters' => $possibleSubmitters,
            'locations' => $locations,
            'summary' => $this->summary($dailyReport),
            'todayBreakdown' => $this->todayBreakdown($dailyReport),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyReport $dailyReport): RedirectResponse
    {
        return redirect()->route('daily-reports.show', $dailyReport);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDailyReportRequest $request, DailyReport $dailyReport): RedirectResponse
    {
        $this->ensureReportIsEditable($dailyReport);

        $validated = $request->validated();
        $user = $request->user();

        $nextStatus = $dailyReport->status;
        if ($user?->hasAnyRole(['glavni_admin', 'administrator_klinike']) && isset($validated['status'])) {
            $nextStatus = $validated['status'];
        }

        $dailyReport->update([
            'report_date' => $validated['report_date'],
            'location_id' => $validated['location_id'],
            'notes' => $validated['notes'] ?? null,
            'last_edited_by_user_id' => $request->user()?->id,
            'status' => $nextStatus,
        ]);

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('status', 'Zaglavlje izvjestaja je uspjesno azurirano.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyReport $dailyReport): RedirectResponse
    {
        $this->ensureReportIsEditable($dailyReport);

        if ($dailyReport->items()->exists() || $dailyReport->findingItems()->exists()) {
            throw ValidationException::withMessages([
                'report' => 'Izvjestaj sa stavkama se ne moze obrisati.',
            ]);
        }

        $dailyReport->delete();

        return redirect()
            ->route('daily-reports.index')
            ->with('status', 'Dnevni izvjestaj je obrisan.');
    }

    public function storeItem(
        StoreDailyReportItemRequest $request,
        DailyReport $dailyReport,
        AuditLogService $auditLogService
    ): RedirectResponse
    {
        $this->ensureReportIsEditable($dailyReport);

        $validated = $request->validated();
        $actor = $request->user();
        $patient = Patient::query()->findOrFail((int) $validated['patient_id']);

        [$paymentStatus, $paidAmount, $remainingAmount, $paymentMethod, $unpaidReason] = $this->normalizePaymentInput($validated);

        $item = DailyReportItem::query()->create([
            'daily_report_id' => $dailyReport->id,
            'patient_id' => $patient->id,
            'patient_full_name' => $patient->full_name,
            'service_id' => $validated['service_id'],
            'doctor_id' => $validated['doctor_id'] ?: null,
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
            'Dodana stavka usluge u izvjestaj #'.$dailyReport->id
        );

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('status', 'Stavka usluge je uspjesno dodana.');
    }

    public function editItem(DailyReport $dailyReport, DailyReportItem $item): View
    {
        $this->ensureReportIsEditable($dailyReport);
        $this->ensureItemBelongsToReport($dailyReport, $item);

        $locationId = $dailyReport->location_id;

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $doctors = StaffMember::query()
            ->where('is_active', true)
            ->whereIn('role_type', ['primarni_doktor', 'sekundarni_doktor', 'saradnik'])
            ->whereHas('locations', fn ($query) => $query->where('locations.id', $locationId))
            ->orderBy('full_name')
            ->get();

        $paymentMethods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $patients = Patient::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        return view('daily-reports.edit-item', [
            'dailyReport' => $dailyReport,
            'item' => $item,
            'services' => $services,
            'doctors' => $doctors,
            'paymentMethods' => $paymentMethods,
            'patients' => $patients,
        ]);
    }

    public function updateItem(
        StoreDailyReportItemRequest $request,
        DailyReport $dailyReport,
        DailyReportItem $item,
        AuditLogService $auditLogService
    ): RedirectResponse
    {
        $this->ensureReportIsEditable($dailyReport);
        $this->ensureItemBelongsToReport($dailyReport, $item);

        $validated = $request->validated();
        $actor = $request->user();
        $patient = Patient::query()->findOrFail((int) $validated['patient_id']);
        $oldValues = [
            'report' => $this->reportItemAuditContext($dailyReport),
            'item' => $this->serviceItemAuditPayload($item),
        ];

        [$paymentStatus, $paidAmount, $remainingAmount, $paymentMethod, $unpaidReason] = $this->normalizePaymentInput($validated);

        $item->update([
            'patient_id' => $patient->id,
            'patient_full_name' => $patient->full_name,
            'service_id' => $validated['service_id'],
            'doctor_id' => $validated['doctor_id'] ?: null,
            'item_price' => (float) $validated['item_price'],
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'unpaid_reason' => $unpaidReason,
            'notes' => $validated['notes'] ?? null,
        ]);

        $dailyReport->update([
            'last_edited_by_user_id' => $actor?->id,
        ]);

        $item->refresh();
        $auditLogService->log(
            $actor,
            'daily_report_items',
            $item->id,
            'updated',
            $oldValues,
            [
                'report' => $this->reportItemAuditContext($dailyReport),
                'item' => $this->serviceItemAuditPayload($item),
            ],
            'Azurirana stavka usluge u izvjestaju #'.$dailyReport->id
        );

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('status', 'Stavka usluge je uspjesno azurirana.');
    }

    public function destroyItem(
        Request $request,
        DailyReport $dailyReport,
        DailyReportItem $item,
        AuditLogService $auditLogService
    ): RedirectResponse
    {
        $this->ensureReportIsEditable($dailyReport);
        $this->ensureItemBelongsToReport($dailyReport, $item);
        $actor = $request->user();
        $oldValues = [
            'report' => $this->reportItemAuditContext($dailyReport),
            'item' => $this->serviceItemAuditPayload($item),
        ];

        $item->delete();
        $dailyReport->update([
            'last_edited_by_user_id' => $actor?->id,
        ]);

        $auditLogService->log(
            $actor,
            'daily_report_items',
            $item->id,
            'deleted',
            $oldValues,
            null,
            'Obrisana stavka usluge iz izvjestaja #'.$dailyReport->id
        );

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('status', 'Stavka usluge je uklonjena.');
    }

    public function storeFindingItem(
        StoreDailyReportFindingItemRequest $request,
        DailyReport $dailyReport,
        AuditLogService $auditLogService
    ): RedirectResponse
    {
        $this->ensureReportIsEditable($dailyReport);

        $validated = $request->validated();
        $actor = $request->user();
        $finding = Finding::query()->findOrFail($validated['finding_id']);

        $quantity = (int) $validated['quantity'];
        $unitPrice = $validated['unit_price'] ?? $finding->unit_price ?? 0;
        $unitPrice = (float) $unitPrice;
        $totalPrice = round($unitPrice * $quantity, 2);

        $findingItem = DailyReportFindingItem::query()->create([
            'daily_report_id' => $dailyReport->id,
            'finding_id' => $finding->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'notes' => $validated['notes'] ?? null,
            'entered_by_user_id' => $actor?->id,
        ]);

        $dailyReport->update([
            'last_edited_by_user_id' => $actor?->id,
        ]);

        $auditLogService->log(
            $actor,
            'daily_report_finding_items',
            $findingItem->id,
            'created',
            null,
            [
                'report' => $this->reportItemAuditContext($dailyReport),
                'item' => $this->findingItemAuditPayload($findingItem),
            ],
            'Dodana stavka nalaza u izvjestaj #'.$dailyReport->id
        );

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('status', 'Stavka nalaza je uspjesno dodana.');
    }

    public function destroyFindingItem(
        Request $request,
        DailyReport $dailyReport,
        DailyReportFindingItem $findingItem,
        AuditLogService $auditLogService
    ): RedirectResponse
    {
        $this->ensureReportIsEditable($dailyReport);

        if ((int) $findingItem->daily_report_id !== (int) $dailyReport->id) {
            abort(404);
        }

        $actor = $request->user();
        $oldValues = [
            'report' => $this->reportItemAuditContext($dailyReport),
            'item' => $this->findingItemAuditPayload($findingItem),
        ];

        $findingItem->delete();
        $dailyReport->update([
            'last_edited_by_user_id' => $actor?->id,
        ]);

        $auditLogService->log(
            $actor,
            'daily_report_finding_items',
            $findingItem->id,
            'deleted',
            $oldValues,
            null,
            'Obrisana stavka nalaza iz izvjestaja #'.$dailyReport->id
        );

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('status', 'Stavka nalaza je uklonjena.');
    }

    public function submit(Request $request, DailyReport $dailyReport, AuditLogService $auditLogService): RedirectResponse
    {
        $this->ensureReportIsEditable($dailyReport);

        $user = $request->user();

        if (! $user?->can_submit_report) {
            throw ValidationException::withMessages([
                'submit' => 'Nemate dozvolu za podnosenje izvjestaja.',
            ]);
        }

        if (! $dailyReport->items()->exists() && ! $dailyReport->findingItems()->exists()) {
            throw ValidationException::withMessages([
                'submit' => 'Izvjestaj ne moze biti podnesen bez ijedne stavke.',
            ]);
        }

        $submittedByUserId = $user->id;
        if ($user->can_change_submitter && $request->filled('submitted_by_user_id')) {
            $submittedByUserId = (int) $request->input('submitted_by_user_id');
        }

        $validSubmitter = User::query()
            ->where('id', $submittedByUserId)
            ->where('is_active', true)
            ->exists();

        if (! $validSubmitter) {
            throw ValidationException::withMessages([
                'submitted_by_user_id' => 'Odabrani podnosilac nije validan.',
            ]);
        }

        $oldValues = $this->reportSubmissionAuditPayload($dailyReport);

        $dailyReport->update([
            'status' => 'podnesen',
            'submitted_at' => Carbon::now(),
            'submitted_by_user_id' => $submittedByUserId,
            'last_edited_by_user_id' => $user->id,
        ]);

        $dailyReport->refresh();
        $auditLogService->log(
            $user,
            'daily_reports',
            $dailyReport->id,
            'submitted',
            $oldValues,
            $this->reportSubmissionAuditPayload($dailyReport),
            'Podnesen dnevni izvjestaj #'.$dailyReport->id
        );

        SendDailyReportEmailJob::dispatch($dailyReport->id);

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('status', 'Dnevni izvjestaj je podnesen.');
    }

    public function reopen(Request $request, DailyReport $dailyReport, AuditLogService $auditLogService): RedirectResponse
    {
        $user = $request->user();

        if (! $user?->hasAnyRole(['glavni_admin', 'administrator_klinike'])) {
            abort(403, 'Nemate dozvolu za vracanje izvjestaja u rad.');
        }

        if ($dailyReport->status === 'zakljucan') {
            throw ValidationException::withMessages([
                'status' => 'Zakljucan izvjestaj se ne moze vratiti u rad bez dodatne administrativne akcije.',
            ]);
        }

        $oldValues = $this->reportSubmissionAuditPayload($dailyReport);

        $dailyReport->update([
            'status' => 'u_radu',
            'last_edited_by_user_id' => $user->id,
        ]);

        $dailyReport->refresh();
        $auditLogService->log(
            $user,
            'daily_reports',
            $dailyReport->id,
            'reopened',
            $oldValues,
            $this->reportSubmissionAuditPayload($dailyReport),
            'Izvjestaj #'.$dailyReport->id.' je vracen u rad.'
        );

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('status', 'Izvjestaj je vracen u status u_radu.');
    }

    private function ensureReportIsEditable(DailyReport $dailyReport): void
    {
        if ($dailyReport->status === 'zakljucan' || $dailyReport->locked_at !== null) {
            throw ValidationException::withMessages([
                'report' => 'Izvjestaj je zakljucan i nije ga moguce mijenjati.',
            ]);
        }
    }

    private function ensureItemBelongsToReport(DailyReport $dailyReport, DailyReportItem $item): void
    {
        if ((int) $item->daily_report_id !== (int) $dailyReport->id) {
            abort(404);
        }
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
     * @return array<string, float|int>
     */
    private function summary(DailyReport $dailyReport): array
    {
        $serviceItems = $dailyReport->items;
        $findingItems = $dailyReport->findingItems;

        $servicesAmount = (float) $serviceItems->sum('item_price');
        $paidAmount = (float) $serviceItems->sum('paid_amount');
        $remainingAmount = (float) $serviceItems->sum('remaining_amount');
        $findingsAmount = (float) $findingItems->sum('total_price');

        return [
            'services_count' => $serviceItems->count(),
            'services_amount' => round($servicesAmount, 2),
            'paid_amount' => round($paidAmount, 2),
            'remaining_amount' => round($remainingAmount, 2),
            'findings_count' => $findingItems->count(),
            'findings_amount' => round($findingsAmount, 2),
            'grand_total' => round($servicesAmount + $findingsAmount, 2),
        ];
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

    /**
     * @return array<string, mixed>
     */
    private function findingItemAuditPayload(DailyReportFindingItem $findingItem): array
    {
        return [
            'daily_report_id' => (int) $findingItem->daily_report_id,
            'finding_id' => (int) $findingItem->finding_id,
            'quantity' => (int) $findingItem->quantity,
            'unit_price' => (float) $findingItem->unit_price,
            'total_price' => (float) $findingItem->total_price,
            'notes' => $findingItem->notes,
            'entered_by_user_id' => $findingItem->entered_by_user_id !== null
                ? (int) $findingItem->entered_by_user_id
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reportSubmissionAuditPayload(DailyReport $dailyReport): array
    {
        return [
            'report_date' => $dailyReport->report_date?->toDateString(),
            'location_id' => (int) $dailyReport->location_id,
            'status' => $dailyReport->status,
            'submitted_at' => $dailyReport->submitted_at?->toAtomString(),
            'submitted_by_user_id' => $dailyReport->submitted_by_user_id !== null
                ? (int) $dailyReport->submitted_by_user_id
                : null,
            'last_edited_by_user_id' => $dailyReport->last_edited_by_user_id !== null
                ? (int) $dailyReport->last_edited_by_user_id
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function todayBreakdown(DailyReport $dailyReport): array
    {
        $items = $dailyReport->items;

        $byService = $items
            ->groupBy(fn (DailyReportItem $item): string => $item->service?->name ?? 'Bez usluge')
            ->map(function ($groupedItems, string $serviceName): array {
                return [
                    'name' => $serviceName,
                    'count' => $groupedItems->count(),
                    'amount' => round((float) $groupedItems->sum('item_price'), 2),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();

        $byDoctor = $items
            ->groupBy(fn (DailyReportItem $item): string => $item->doctor?->full_name ?? 'Bez doktora')
            ->map(function ($groupedItems, string $doctorName): array {
                return [
                    'name' => $doctorName,
                    'count' => $groupedItems->count(),
                    'amount' => round((float) $groupedItems->sum('item_price'), 2),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();

        $methodLabels = [
            'fiskalno' => 'Fiskalno',
            'nefiskalno' => 'Nefiskalno',
            'karticno' => 'Karticno',
            'ziralno' => 'Ziralno',
            'nepoznato' => 'Nepoznato',
        ];

        $paidItems = $items->filter(
            fn (DailyReportItem $item): bool => in_array(
                (string) $item->payment_status,
                ['placeno', 'djelimicno_placeno'],
                true
            )
        );

        $byPaymentMethod = $paidItems
            ->groupBy(function (DailyReportItem $item): string {
                $method = trim((string) ($item->payment_method ?? ''));

                return $method !== '' ? $method : 'nepoznato';
            })
            ->map(function ($groupedItems, string $methodKey) use ($methodLabels): array {
                return [
                    'method_key' => $methodKey,
                    'method_label' => $methodLabels[$methodKey] ?? ucfirst($methodKey),
                    'count' => $groupedItems->count(),
                    'paid_amount' => round((float) $groupedItems->sum('paid_amount'), 2),
                ];
            })
            ->sortByDesc('paid_amount')
            ->values()
            ->all();

        $fullyUnpaidItems = $items->where('payment_status', 'neplaceno');
        $partiallyPaidItems = $items->where('payment_status', 'djelimicno_placeno');

        return [
            'total_items_count' => $items->count(),
            'total_amount' => round((float) $items->sum('item_price'), 2),
            'paid_amount' => round((float) $items->sum('paid_amount'), 2),
            'remaining_amount' => round((float) $items->sum('remaining_amount'), 2),
            'fully_unpaid_count' => $fullyUnpaidItems->count(),
            'fully_unpaid_amount' => round((float) $fullyUnpaidItems->sum('remaining_amount'), 2),
            'partially_paid_count' => $partiallyPaidItems->count(),
            'partially_paid_remaining_amount' => round((float) $partiallyPaidItems->sum('remaining_amount'), 2),
            'by_service' => $byService,
            'by_doctor' => $byDoctor,
            'by_payment_method' => $byPaymentMethod,
        ];
    }
}
