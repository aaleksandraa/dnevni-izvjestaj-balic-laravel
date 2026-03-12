<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\DailyReportFindingItem;
use App\Models\DailyReportItem;
use Illuminate\Support\Carbon;

class ReportSummaryService
{
    /**
     * @return array<string, float|int|string>
     */
    public function daily(DailyReport $dailyReport): array
    {
        $servicesCount = (int) $dailyReport->items()->count();
        $servicesAmount = (float) $dailyReport->items()->sum('item_price');
        $paidAmount = (float) $dailyReport->items()->sum('paid_amount');
        $remainingAmount = (float) $dailyReport->items()->sum('remaining_amount');
        $unpaidItemsCount = (int) $dailyReport->items()->where('payment_status', 'neplaceno')->count();
        $partialItemsCount = (int) $dailyReport->items()->where('payment_status', 'djelimicno_placeno')->count();

        $findingsCount = (int) $dailyReport->findingItems()->sum('quantity');
        $findingsAmount = (float) $dailyReport->findingItems()->sum('total_price');

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
