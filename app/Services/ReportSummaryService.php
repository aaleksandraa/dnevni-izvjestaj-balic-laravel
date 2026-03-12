<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\DailyReportFindingItem;
use App\Models\DailyReportItem;
use App\Models\Service;
use App\Models\StaffMember;
use Illuminate\Support\Carbon;

class ReportSummaryService
{
    public function __construct(
        private readonly DailyEmailSummaryConfigurationService $configurationService
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function daily(DailyReport $dailyReport): array
    {
        $configuration = $this->configurationService->get();

        $items = $dailyReport->items()
            ->with(['service:id,name', 'doctor:id,full_name,role_type'])
            ->get();

        $servicesCount = (int) $items->count();
        $servicesAmount = (float) $items->sum('item_price');
        $paidAmount = (float) $items->sum('paid_amount');
        $remainingAmount = (float) $items->sum('remaining_amount');
        $unpaidItemsCount = (int) $items->where('payment_status', 'neplaceno')->count();
        $partialItemsCount = (int) $items->where('payment_status', 'djelimicno_placeno')->count();

        $findingsCount = (int) $dailyReport->findingItems()->sum('quantity');
        $findingsAmount = (float) $dailyReport->findingItems()->sum('total_price');

        $selectedServices = Service::query()
            ->whereIn('id', $configuration['service_ids'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $bySignificantService = $selectedServices
            ->map(function (Service $service) use ($items): array {
                return [
                    'service_id' => (int) $service->id,
                    'name' => $service->name,
                    'count' => (int) $items->where('service_id', $service->id)->count(),
                ];
            })
            ->all();

        $significantServicesTotalCount = array_reduce(
            $bySignificantService,
            static fn (int $carry, array $row): int => $carry + (int) $row['count'],
            0
        );

        $selectedCollaborators = StaffMember::query()
            ->whereIn('id', $configuration['collaborator_ids'])
            ->where('is_active', true)
            ->where('role_type', 'saradnik')
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $byCollaborator = $selectedCollaborators
            ->map(function (StaffMember $staffMember) use ($items): array {
                return [
                    'staff_member_id' => (int) $staffMember->id,
                    'name' => $staffMember->full_name,
                    'count' => (int) $items->where('doctor_id', $staffMember->id)->count(),
                ];
            })
            ->all();

        $collaboratorsTotalCount = array_reduce(
            $byCollaborator,
            static fn (int $carry, array $row): int => $carry + (int) $row['count'],
            0
        );

        $selectedLeadDoctors = StaffMember::query()
            ->whereIn('id', $configuration['lead_doctor_ids'])
            ->where('is_active', true)
            ->whereIn('role_type', ['primarni_doktor', 'sekundarni_doktor'])
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $byLeadDoctor = $selectedLeadDoctors
            ->map(function (StaffMember $staffMember) use ($items): array {
                return [
                    'staff_member_id' => (int) $staffMember->id,
                    'name' => $staffMember->full_name,
                    'count' => (int) $items->where('doctor_id', $staffMember->id)->count(),
                ];
            })
            ->all();

        $leadDoctorsTotalCount = array_reduce(
            $byLeadDoctor,
            static fn (int $carry, array $row): int => $carry + (int) $row['count'],
            0
        );

        $newPatientsCount = (int) $items->where('is_new_patient', true)->count();

        return [
            'report_date' => $dailyReport->report_date->toDateString(),
            'location_name' => $dailyReport->location?->name ?? '-',
            'status' => $dailyReport->status,
            'services_count' => $servicesCount,
            'services_amount' => round($servicesAmount, 2),
            'paid_amount' => round($paidAmount, 2),
            'remaining_amount' => round($remainingAmount, 2),
            'unpaid_items_count' => $unpaidItemsCount,
            'partial_items_count' => $partialItemsCount,
            'findings_count' => $findingsCount,
            'findings_amount' => round($findingsAmount, 2),
            'grand_total' => round($servicesAmount + $findingsAmount, 2),
            'collaborators_total_count' => $collaboratorsTotalCount,
            'by_collaborator' => $byCollaborator,
            'lead_doctors_total_count' => $leadDoctorsTotalCount,
            'by_lead_doctor' => $byLeadDoctor,
            'significant_services_total_count' => $significantServicesTotalCount,
            'by_significant_service' => $bySignificantService,
            'include_new_patients' => (bool) $configuration['include_new_patients'],
            'new_patients_count' => $newPatientsCount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function period(Carbon $startDate, Carbon $endDate): array
    {
        $reportsQuery = DailyReport::query()
            ->whereDate('report_date', '>=', $startDate->toDateString())
            ->whereDate('report_date', '<=', $endDate->toDateString());

        $reportsCount = (int) (clone $reportsQuery)->count();
        $reportIds = (clone $reportsQuery)->pluck('id');

        $servicesQuery = DailyReportItem::query()
            ->whereIn('daily_report_id', $reportIds);

        $findingsQuery = DailyReportFindingItem::query()
            ->whereIn('daily_report_id', $reportIds);

        $servicesCount = (int) (clone $servicesQuery)->count();
        $servicesAmount = (float) (clone $servicesQuery)->sum('item_price');
        $paidAmount = (float) (clone $servicesQuery)->sum('paid_amount');
        $remainingAmount = (float) (clone $servicesQuery)->sum('remaining_amount');
        $unpaidItemsCount = (int) (clone $servicesQuery)->where('payment_status', 'neplaceno')->count();
        $partialItemsCount = (int) (clone $servicesQuery)->where('payment_status', 'djelimicno_placeno')->count();

        $findingsCount = (int) (clone $findingsQuery)->sum('quantity');
        $findingsAmount = (float) (clone $findingsQuery)->sum('total_price');

        $locationRows = DailyReport::query()
            ->leftJoin('daily_report_items', 'daily_report_items.daily_report_id', '=', 'daily_reports.id')
            ->leftJoin('locations', 'locations.id', '=', 'daily_reports.location_id')
            ->whereDate('daily_reports.report_date', '>=', $startDate->toDateString())
            ->whereDate('daily_reports.report_date', '<=', $endDate->toDateString())
            ->groupBy('daily_reports.location_id', 'locations.name')
            ->orderBy('locations.name')
            ->get([
                'daily_reports.location_id',
                'locations.name as location_name',
            ])
            ->map(function ($row): array {
                return [
                    'location_id' => (int) $row->location_id,
                    'location_name' => (string) ($row->location_name ?? '-'),
                ];
            })
            ->unique('location_id')
            ->values();

        $byLocation = $locationRows->map(function (array $locationRow) use ($startDate, $endDate): array {
            $locationId = (int) $locationRow['location_id'];

            $locationReports = DailyReport::query()
                ->where('location_id', $locationId)
                ->whereDate('report_date', '>=', $startDate->toDateString())
                ->whereDate('report_date', '<=', $endDate->toDateString());

            $locationReportIds = (clone $locationReports)->pluck('id');

            $locationServices = DailyReportItem::query()
                ->whereIn('daily_report_id', $locationReportIds);
            $locationFindings = DailyReportFindingItem::query()
                ->whereIn('daily_report_id', $locationReportIds);

            $servicesAmount = (float) (clone $locationServices)->sum('item_price');
            $findingsAmount = (float) (clone $locationFindings)->sum('total_price');

            return [
                'location_name' => $locationRow['location_name'],
                'reports_count' => (int) (clone $locationReports)->count(),
                'services_count' => (int) (clone $locationServices)->count(),
                'services_amount' => round($servicesAmount, 2),
                'paid_amount' => round((float) (clone $locationServices)->sum('paid_amount'), 2),
                'remaining_amount' => round((float) (clone $locationServices)->sum('remaining_amount'), 2),
                'findings_count' => (int) (clone $locationFindings)->sum('quantity'),
                'findings_amount' => round($findingsAmount, 2),
                'grand_total' => round($servicesAmount + $findingsAmount, 2),
            ];
        })->all();

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'reports_count' => $reportsCount,
            'services_count' => $servicesCount,
            'services_amount' => round($servicesAmount, 2),
            'paid_amount' => round($paidAmount, 2),
            'remaining_amount' => round($remainingAmount, 2),
            'unpaid_items_count' => $unpaidItemsCount,
            'partial_items_count' => $partialItemsCount,
            'findings_count' => $findingsCount,
            'findings_amount' => round($findingsAmount, 2),
            'grand_total' => round($servicesAmount + $findingsAmount, 2),
            'by_location' => $byLocation,
        ];
    }
}
