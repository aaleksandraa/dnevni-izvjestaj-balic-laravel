<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DailyReport;
use App\Models\Finding;
use App\Models\FindingCategory;
use App\Models\DailyReportItem;
use App\Models\DailyReportFindingItem;
use App\Models\Location;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AuditLogHookTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_update_creates_audit_log_entry(): void
    {
        $admin = User::factory()->create([
            'role' => 'glavni_admin',
            'is_active' => true,
        ]);

        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $targetUser = User::factory()->create([
            'name' => 'Staro Ime',
            'email' => 'old.user@example.com',
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);
        $targetUser->locations()->sync([$locationA->id]);

        $this->actingAs($admin)
            ->put(route('users.update', $targetUser), [
                'name' => 'Novo Ime',
                'email' => 'new.user@example.com',
                'password' => '',
                'password_confirmation' => '',
                'role' => 'medicinska_sestra',
                'phone' => '061223344',
                'is_active' => '1',
                'can_submit_report' => '1',
                'can_change_submitter' => '1',
                'location_ids' => [$locationA->id, $locationB->id],
            ])
            ->assertRedirect(route('users.index'));

        $auditLog = AuditLog::query()
            ->where('entity_type', 'users')
            ->where('entity_id', $targetUser->id)
            ->where('action', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame('Staro Ime', $auditLog->old_values['name']);
        $this->assertSame('Novo Ime', $auditLog->new_values['name']);
    }

    public function test_report_submit_creates_audit_log_entry(): void
    {
        Queue::fake();

        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
            'can_submit_report' => true,
            'can_change_submitter' => false,
        ]);

        $location = Location::factory()->create();
        $service = Service::factory()->create([
            'service_category_id' => ServiceCategory::factory()->create()->id,
            'is_active' => true,
        ]);
        $doctor = StaffMember::factory()->create([
            'is_active' => true,
        ]);
        $doctor->locations()->sync([$location->id]);

        $report = DailyReport::factory()->create([
            'location_id' => $location->id,
            'status' => 'u_radu',
            'created_by_user_id' => $nurse->id,
            'last_edited_by_user_id' => $nurse->id,
        ]);

        DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'item_price' => 100,
            'paid_amount' => 100,
            'remaining_amount' => 0,
            'payment_status' => 'placeno',
            'payment_method' => 'fiskalno',
            'unpaid_reason' => null,
            'entered_by_user_id' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->post(route('daily-reports.submit', $report))
            ->assertRedirect(route('daily-reports.show', $report));

        $auditLog = AuditLog::query()
            ->where('entity_type', 'daily_reports')
            ->where('entity_id', $report->id)
            ->where('action', 'submitted')
            ->latest('id')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame('u_radu', $auditLog->old_values['status']);
        $this->assertSame('podnesen', $auditLog->new_values['status']);
        $this->assertSame($nurse->id, $auditLog->user_id);
    }

    public function test_service_item_create_and_delete_are_audited(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
            'can_submit_report' => true,
        ]);

        $location = Location::factory()->create();
        $service = Service::factory()->create([
            'service_category_id' => ServiceCategory::factory()->create()->id,
            'is_active' => true,
        ]);
        $doctor = StaffMember::factory()->create([
            'is_active' => true,
        ]);
        $doctor->locations()->sync([$location->id]);
        $patient = Patient::factory()->create([
            'full_name' => 'Pacijent Audit',
            'is_active' => true,
        ]);

        $report = DailyReport::factory()->create([
            'location_id' => $location->id,
            'status' => 'u_radu',
            'created_by_user_id' => $nurse->id,
            'last_edited_by_user_id' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->post(route('daily-reports.items.store', $report), [
                'patient_id' => $patient->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'item_price' => 120,
                'payment_status' => 'placeno',
                'payment_method' => 'fiskalno',
                'paid_amount' => 120,
                'notes' => 'Audit test',
            ])
            ->assertRedirect(route('daily-reports.show', $report));

        $item = DailyReportItem::query()->where('daily_report_id', $report->id)->firstOrFail();

        $createdLog = AuditLog::query()
            ->where('entity_type', 'daily_report_items')
            ->where('entity_id', $item->id)
            ->where('action', 'created')
            ->latest('id')
            ->first();

        $this->assertNotNull($createdLog);
        $this->assertSame($patient->id, $createdLog->new_values['item']['patient_id']);
        $this->assertSame('Pacijent Audit', $createdLog->new_values['item']['patient_full_name']);

        $this->actingAs($nurse)
            ->delete(route('daily-reports.items.destroy', [$report, $item]))
            ->assertRedirect(route('daily-reports.show', $report));

        $deletedLog = AuditLog::query()
            ->where('entity_type', 'daily_report_items')
            ->where('entity_id', $item->id)
            ->where('action', 'deleted')
            ->latest('id')
            ->first();

        $this->assertNotNull($deletedLog);
        $this->assertSame($patient->id, $deletedLog->old_values['item']['patient_id']);
        $this->assertSame('Pacijent Audit', $deletedLog->old_values['item']['patient_full_name']);
    }

    public function test_service_item_update_is_audited(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
            'can_submit_report' => true,
        ]);

        $location = Location::factory()->create();
        $serviceCategory = ServiceCategory::factory()->create();
        $serviceA = Service::factory()->create([
            'service_category_id' => $serviceCategory->id,
            'is_active' => true,
        ]);
        $serviceB = Service::factory()->create([
            'service_category_id' => $serviceCategory->id,
            'is_active' => true,
        ]);
        $doctor = StaffMember::factory()->create([
            'is_active' => true,
        ]);
        $doctor->locations()->sync([$location->id]);
        $patientBefore = Patient::factory()->create([
            'full_name' => 'Pacijent Prije',
            'is_active' => true,
        ]);
        $patientAfter = Patient::factory()->create([
            'full_name' => 'Pacijent Poslije',
            'is_active' => true,
        ]);

        $report = DailyReport::factory()->create([
            'location_id' => $location->id,
            'status' => 'u_radu',
            'created_by_user_id' => $nurse->id,
            'last_edited_by_user_id' => $nurse->id,
        ]);

        $item = DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'patient_id' => $patientBefore->id,
            'patient_full_name' => 'Pacijent Prije',
            'service_id' => $serviceA->id,
            'doctor_id' => $doctor->id,
            'item_price' => 100,
            'paid_amount' => 100,
            'remaining_amount' => 0,
            'payment_status' => 'placeno',
            'payment_method' => 'fiskalno',
            'unpaid_reason' => null,
            'entered_by_user_id' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->put(route('daily-reports.items.update', [$report, $item]), [
                'patient_id' => $patientAfter->id,
                'service_id' => $serviceB->id,
                'doctor_id' => $doctor->id,
                'item_price' => 150,
                'payment_status' => 'djelimicno_placeno',
                'payment_method' => 'karticno',
                'paid_amount' => 50,
                'unpaid_reason' => 'Dopuna kasnije',
            ])
            ->assertRedirect(route('daily-reports.show', $report));

        $updatedLog = AuditLog::query()
            ->where('entity_type', 'daily_report_items')
            ->where('entity_id', $item->id)
            ->where('action', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($updatedLog);
        $this->assertSame($patientBefore->id, $updatedLog->old_values['item']['patient_id']);
        $this->assertSame($patientAfter->id, $updatedLog->new_values['item']['patient_id']);
        $this->assertSame('Pacijent Prije', $updatedLog->old_values['item']['patient_full_name']);
        $this->assertSame('Pacijent Poslije', $updatedLog->new_values['item']['patient_full_name']);
    }

    public function test_finding_item_create_and_delete_are_audited(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
            'can_submit_report' => true,
        ]);

        $location = Location::factory()->create();
        $service = Service::factory()->create([
            'service_category_id' => ServiceCategory::factory()->create()->id,
            'is_active' => true,
        ]);
        $finding = Finding::factory()->create([
            'finding_category_id' => FindingCategory::factory()->create()->id,
            'service_id' => $service->id,
            'is_active' => true,
            'unit_price' => 15,
        ]);

        $report = DailyReport::factory()->create([
            'location_id' => $location->id,
            'status' => 'u_radu',
            'created_by_user_id' => $nurse->id,
            'last_edited_by_user_id' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->post(route('daily-reports.finding-items.store', $report), [
                'finding_id' => $finding->id,
                'quantity' => 3,
                'unit_price' => 15,
                'notes' => 'Audit nalaz',
            ])
            ->assertRedirect(route('daily-reports.show', $report));

        $findingItem = DailyReportFindingItem::query()
            ->where('daily_report_id', $report->id)
            ->firstOrFail();

        $createdLog = AuditLog::query()
            ->where('entity_type', 'daily_report_finding_items')
            ->where('entity_id', $findingItem->id)
            ->where('action', 'created')
            ->latest('id')
            ->first();

        $this->assertNotNull($createdLog);
        $this->assertSame(3, $createdLog->new_values['item']['quantity']);

        $this->actingAs($nurse)
            ->delete(route('daily-reports.finding-items.destroy', [$report, $findingItem]))
            ->assertRedirect(route('daily-reports.show', $report));

        $deletedLog = AuditLog::query()
            ->where('entity_type', 'daily_report_finding_items')
            ->where('entity_id', $findingItem->id)
            ->where('action', 'deleted')
            ->latest('id')
            ->first();

        $this->assertNotNull($deletedLog);
        $this->assertSame(3, $deletedLog->old_values['item']['quantity']);
    }
}
