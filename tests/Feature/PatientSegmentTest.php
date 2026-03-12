<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\Location;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientSegmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_nurse_can_view_patient_list_with_aggregates(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $location = Location::factory()->create();
        $service = Service::factory()->create([
            'service_category_id' => ServiceCategory::factory()->create()->id,
        ]);
        $doctor = StaffMember::factory()->create();
        $doctor->locations()->sync([$location->id]);
        $patient = Patient::factory()->create([
            'full_name' => 'Pacijent Segment',
            'is_active' => true,
        ]);

        $report = DailyReport::factory()->create([
            'location_id' => $location->id,
            'status' => 'u_radu',
            'created_by_user_id' => $nurse->id,
        ]);

        DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'patient_id' => $patient->id,
            'patient_full_name' => 'Pacijent Segment',
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'item_price' => 100,
            'payment_status' => 'placeno',
            'payment_method' => 'fiskalno',
            'paid_amount' => 100,
            'remaining_amount' => 0,
            'unpaid_reason' => null,
            'entered_by_user_id' => $nurse->id,
        ]);

        DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'patient_id' => $patient->id,
            'patient_full_name' => 'Pacijent Segment',
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'item_price' => 90,
            'payment_status' => 'djelimicno_placeno',
            'payment_method' => 'karticno',
            'paid_amount' => 40,
            'remaining_amount' => 50,
            'unpaid_reason' => 'Ostatak iduce sedmice',
            'entered_by_user_id' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->get(route('patients.index'))
            ->assertOk()
            ->assertSee('Pacijent Segment')
            ->assertSee('190,00')
            ->assertSee('140,00')
            ->assertSee('50,00');
    }

    public function test_patient_ledger_shows_exams_payments_and_debts(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $location = Location::factory()->create([
            'name' => 'IVF Lokacija A',
        ]);
        $service = Service::factory()->create([
            'service_category_id' => ServiceCategory::factory()->create()->id,
            'name' => 'Kontrolni pregled',
        ]);
        $doctor = StaffMember::factory()->create([
            'full_name' => 'Dr Segment',
        ]);
        $doctor->locations()->sync([$location->id]);
        $patient = Patient::factory()->create([
            'full_name' => 'Pacijent Karton',
            'is_active' => true,
        ]);

        $report = DailyReport::factory()->create([
            'location_id' => $location->id,
            'status' => 'u_radu',
            'created_by_user_id' => $nurse->id,
            'report_date' => now()->toDateString(),
        ]);

        DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'patient_id' => $patient->id,
            'patient_full_name' => 'Pacijent Karton',
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'item_price' => 120,
            'payment_status' => 'placeno',
            'payment_method' => 'fiskalno',
            'paid_amount' => 120,
            'remaining_amount' => 0,
            'unpaid_reason' => null,
            'entered_by_user_id' => $nurse->id,
        ]);

        DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'patient_id' => $patient->id,
            'patient_full_name' => 'Pacijent Karton',
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'item_price' => 80,
            'payment_status' => 'neplaceno',
            'payment_method' => null,
            'paid_amount' => 0,
            'remaining_amount' => 80,
            'unpaid_reason' => 'Ceka naplatu',
            'entered_by_user_id' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->get(route('patients.show', $patient))
            ->assertOk()
            ->assertSee('Karton pacijenta: Pacijent Karton')
            ->assertSee('Kontrolni pregled')
            ->assertSee('Dr Segment')
            ->assertSee('Fiskalno')
            ->assertSee('Ukupan dug')
            ->assertSee('80,00');
    }

    public function test_guest_is_redirected_from_patients_module(): void
    {
        $this->get(route('patients.index'))
            ->assertRedirect(route('login'));
    }

    public function test_nurse_can_add_payment_from_patient_ledger_to_todays_report(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $location = Location::factory()->create();
        $service = Service::factory()->create([
            'service_category_id' => ServiceCategory::factory()->create()->id,
            'is_active' => true,
            'base_price' => 150,
        ]);
        $doctor = StaffMember::factory()->create([
            'role_type' => 'primarni_doktor',
            'is_active' => true,
        ]);
        $doctor->locations()->sync([$location->id]);
        $patient = Patient::factory()->create([
            'full_name' => 'Pacijent Placanje',
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->post(route('patients.payments.store', $patient), [
                'report_date' => now()->format('d.m.Y'),
                'location_id' => $location->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'item_price' => 150,
                'payment_status' => 'placeno',
                'payment_method' => 'fiskalno',
                'paid_amount' => 150,
                'notes' => 'Dodano iz kartona',
            ])
            ->assertRedirect(route('patients.show', $patient));

        $report = DailyReport::query()
            ->whereDate('report_date', now()->toDateString())
            ->where('location_id', $location->id)
            ->firstOrFail();

        $this->assertDatabaseHas('daily_report_items', [
            'daily_report_id' => $report->id,
            'patient_id' => $patient->id,
            'patient_full_name' => 'Pacijent Placanje',
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'payment_status' => 'placeno',
            'payment_method' => 'fiskalno',
            'paid_amount' => 150,
            'remaining_amount' => 0,
        ]);
    }

    public function test_payment_from_patient_ledger_rejects_non_today_date(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $location = Location::factory()->create();
        $service = Service::factory()->create([
            'service_category_id' => ServiceCategory::factory()->create()->id,
            'is_active' => true,
        ]);
        $doctor = StaffMember::factory()->create([
            'role_type' => 'primarni_doktor',
            'is_active' => true,
        ]);
        $doctor->locations()->sync([$location->id]);
        $patient = Patient::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->post(route('patients.payments.store', $patient), [
                'report_date' => now()->subDay()->format('d.m.Y'),
                'location_id' => $location->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'item_price' => 100,
                'payment_status' => 'placeno',
                'payment_method' => 'fiskalno',
                'paid_amount' => 100,
            ])
            ->assertSessionHasErrors(['report_date']);
    }

    public function test_patient_ledger_displays_european_date_format_in_filters(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $patient = Patient::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->get(route('patients.show', [
                'patient' => $patient,
                'date_from' => '03/26/2026',
                'date_to' => '03/26/2026',
            ]))
            ->assertOk()
            ->assertSee('value="26.03.2026"', false);
    }
}
