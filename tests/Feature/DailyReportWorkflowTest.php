<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\Finding;
use App\Models\FindingCategory;
use App\Models\Location;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_nurse_can_create_daily_report_add_items_and_submit(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
            'can_submit_report' => true,
            'can_change_submitter' => false,
        ]);

        $location = Location::factory()->create();
        $serviceCategory = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'service_category_id' => $serviceCategory->id,
            'base_price' => 100,
            'is_active' => true,
        ]);

        $doctor = StaffMember::factory()->create([
            'role_type' => 'primarni_doktor',
            'is_active' => true,
        ]);
        $doctor->locations()->sync([$location->id]);

        $findingCategory = FindingCategory::factory()->create();
        $finding = Finding::factory()->create([
            'finding_category_id' => $findingCategory->id,
            'service_id' => $service->id,
            'unit_price' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->post(route('daily-reports.store'), [
                'report_date' => now()->toDateString(),
                'location_id' => $location->id,
                'notes' => 'Smjena 1',
            ])
            ->assertRedirect();

        $report = DailyReport::query()->where('location_id', $location->id)->firstOrFail();

        $this->actingAs($nurse)
            ->post(route('daily-reports.items.store', $report), [
                'patient_full_name' => 'Pacijent A',
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'item_price' => 100,
                'payment_status' => 'djelimicno_placeno',
                'payment_method' => 'karticno',
                'paid_amount' => 40,
                'unpaid_reason' => 'Ostatak na sljedecem pregledu',
                'notes' => 'Test stavka',
            ])
            ->assertRedirect(route('daily-reports.show', $report));

        $this->assertDatabaseHas('daily_report_items', [
            'daily_report_id' => $report->id,
            'patient_full_name' => 'Pacijent A',
            'payment_status' => 'djelimicno_placeno',
            'paid_amount' => 40,
            'remaining_amount' => 60,
        ]);

        $this->actingAs($nurse)
            ->post(route('daily-reports.finding-items.store', $report), [
                'finding_id' => $finding->id,
                'quantity' => 2,
                'unit_price' => 10,
                'notes' => 'Dodatni nalaz',
            ])
            ->assertRedirect(route('daily-reports.show', $report));

        $this->assertDatabaseHas('daily_report_finding_items', [
            'daily_report_id' => $report->id,
            'finding_id' => $finding->id,
            'quantity' => 2,
            'unit_price' => 10,
            'total_price' => 20,
        ]);

        $this->actingAs($nurse)
            ->post(route('daily-reports.submit', $report))
            ->assertRedirect(route('daily-reports.show', $report));

        $this->assertDatabaseHas('daily_reports', [
            'id' => $report->id,
            'status' => 'podnesen',
            'submitted_by_user_id' => $nurse->id,
        ]);
    }

    public function test_daily_report_can_be_created_only_for_today(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $location = Location::factory()->create();

        $this->actingAs($nurse)
            ->post(route('daily-reports.store'), [
                'report_date' => now()->subDay()->toDateString(),
                'location_id' => $location->id,
            ])
            ->assertSessionHasErrors(['report_date']);

        $this->actingAs($nurse)
            ->post(route('daily-reports.store'), [
                'report_date' => now()->addDay()->toDateString(),
                'location_id' => $location->id,
            ])
            ->assertSessionHasErrors(['report_date']);
    }

    public function test_nurse_can_edit_existing_service_item(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $location = Location::factory()->create();
        $serviceCategory = ServiceCategory::factory()->create();
        $serviceA = Service::factory()->create([
            'service_category_id' => $serviceCategory->id,
            'is_active' => true,
            'base_price' => 100,
        ]);
        $serviceB = Service::factory()->create([
            'service_category_id' => $serviceCategory->id,
            'is_active' => true,
            'base_price' => 140,
        ]);

        $doctor = StaffMember::factory()->create([
            'role_type' => 'primarni_doktor',
            'is_active' => true,
        ]);
        $doctor->locations()->sync([$location->id]);

        $report = DailyReport::factory()->create([
            'report_date' => now()->toDateString(),
            'location_id' => $location->id,
            'status' => 'u_radu',
            'created_by_user_id' => $nurse->id,
        ]);

        $item = DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'patient_full_name' => 'Pacijent Original',
            'service_id' => $serviceA->id,
            'doctor_id' => $doctor->id,
            'item_price' => 100,
            'payment_status' => 'placeno',
            'payment_method' => 'fiskalno',
            'paid_amount' => 100,
            'remaining_amount' => 0,
            'unpaid_reason' => null,
            'entered_by_user_id' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->put(route('daily-reports.items.update', [$report, $item]), [
                'patient_full_name' => 'Pacijent Azuriran',
                'service_id' => $serviceB->id,
                'doctor_id' => $doctor->id,
                'item_price' => 140,
                'payment_status' => 'djelimicno_placeno',
                'payment_method' => 'karticno',
                'paid_amount' => 70,
                'unpaid_reason' => 'Nastavak naplate',
                'notes' => 'Azurirana stavka',
            ])
            ->assertRedirect(route('daily-reports.show', $report));

        $this->assertDatabaseHas('daily_report_items', [
            'id' => $item->id,
            'patient_full_name' => 'Pacijent Azuriran',
            'service_id' => $serviceB->id,
            'payment_status' => 'djelimicno_placeno',
            'payment_method' => 'karticno',
            'paid_amount' => 70,
            'remaining_amount' => 70,
        ]);
    }

    public function test_show_page_displays_today_breakdown_sections(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $location = Location::factory()->create();
        $serviceCategory = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'service_category_id' => $serviceCategory->id,
            'is_active' => true,
            'name' => 'Konsultacija IVF',
        ]);

        $doctor = StaffMember::factory()->create([
            'role_type' => 'primarni_doktor',
            'is_active' => true,
            'full_name' => 'Dr Test Doktor',
        ]);
        $doctor->locations()->sync([$location->id]);

        $report = DailyReport::factory()->create([
            'report_date' => now()->toDateString(),
            'location_id' => $location->id,
            'status' => 'u_radu',
            'created_by_user_id' => $nurse->id,
        ]);

        DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'patient_full_name' => 'Pacijent R',
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'item_price' => 120,
            'payment_status' => 'placeno',
            'payment_method' => 'karticno',
            'paid_amount' => 120,
            'remaining_amount' => 0,
            'unpaid_reason' => null,
            'entered_by_user_id' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->get(route('daily-reports.show', $report))
            ->assertOk()
            ->assertSee('Danasnja rekapitulacija')
            ->assertSee('Pregledi po uslugama')
            ->assertSee('Pregledi po doktorima')
            ->assertSee('Naplaceno po nacinu')
            ->assertSee('Konsultacija IVF')
            ->assertSee('Dr Test Doktor')
            ->assertSee('Karticno');
    }
}
