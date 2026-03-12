<?php

namespace Tests\Feature;

use App\Jobs\SendDailyReportEmailJob;
use App\Jobs\SendPeriodicReportEmailJob;
use App\Mail\DailyReportSubmittedMail;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\Location;
use App\Models\ReportConfiguration;
use App\Models\ReportEmailSetting;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StaffMember;
use App\Models\User;
use App\Services\ReportSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReportEmailAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_daily_report_dispatches_daily_email_job(): void
    {
        Queue::fake();

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
        $doctor = StaffMember::factory()->create(['is_active' => true]);
        $doctor->locations()->sync([$location->id]);

        $report = DailyReport::factory()->create([
            'location_id' => $location->id,
            'status' => 'u_radu',
            'created_by_user_id' => $nurse->id,
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

        Queue::assertPushed(SendDailyReportEmailJob::class);
    }

    public function test_daily_email_job_sends_mail_to_active_daily_recipients(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'glavni_admin',
            'is_active' => true,
        ]);
        $location = Location::factory()->create();
        $service = Service::factory()->create([
            'service_category_id' => ServiceCategory::factory()->create()->id,
        ]);
        $doctor = StaffMember::factory()->create();
        $doctor->locations()->sync([$location->id]);

        $report = DailyReport::factory()->create([
            'location_id' => $location->id,
            'status' => 'podnesen',
            'created_by_user_id' => $user->id,
            'submitted_by_user_id' => $user->id,
        ]);

        DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'item_price' => 50,
            'paid_amount' => 50,
            'remaining_amount' => 0,
            'payment_status' => 'placeno',
            'payment_method' => 'fiskalno',
            'entered_by_user_id' => $user->id,
        ]);

        ReportEmailSetting::query()->create([
            'report_type' => 'daily',
            'email' => 'daily.active@example.com',
            'is_active' => true,
        ]);
        ReportEmailSetting::query()->create([
            'report_type' => 'daily',
            'email' => 'daily.inactive@example.com',
            'is_active' => false,
        ]);

        $job = new SendDailyReportEmailJob($report->id);
        $job->handle(app(ReportSummaryService::class));

        Mail::assertSent(DailyReportSubmittedMail::class, function (DailyReportSubmittedMail $mail): bool {
            return $mail->hasTo('daily.active@example.com');
        });

        Mail::assertNotSent(DailyReportSubmittedMail::class, function (DailyReportSubmittedMail $mail): bool {
            return $mail->hasTo('daily.inactive@example.com');
        });
    }

    public function test_periodic_commands_dispatch_jobs(): void
    {
        Queue::fake();

        $this->artisan('reports:send-weekly-summary')
            ->assertSuccessful();
        $this->artisan('reports:send-monthly-summary')
            ->assertSuccessful();

        Queue::assertPushed(SendPeriodicReportEmailJob::class, function (SendPeriodicReportEmailJob $job): bool {
            return $job->reportType === 'weekly';
        });

        Queue::assertPushed(SendPeriodicReportEmailJob::class, function (SendPeriodicReportEmailJob $job): bool {
            return $job->reportType === 'monthly';
        });
    }

    public function test_daily_summary_uses_configured_services_staff_and_new_patient_count(): void
    {
        $user = User::factory()->create([
            'role' => 'glavni_admin',
            'is_active' => true,
        ]);
        $location = Location::factory()->create();

        $serviceCategory = ServiceCategory::factory()->create();
        $hsc = Service::factory()->create([
            'service_category_id' => $serviceCategory->id,
            'name' => 'HSC',
            'is_active' => true,
        ]);
        $iui = Service::factory()->create([
            'service_category_id' => $serviceCategory->id,
            'name' => 'IUI',
            'is_active' => true,
        ]);

        $collaborator = StaffMember::factory()->create([
            'full_name' => 'Adem',
            'role_type' => 'saradnik',
            'is_active' => true,
        ]);
        $leadDoctor = StaffMember::factory()->create([
            'full_name' => 'Dela',
            'role_type' => 'primarni_doktor',
            'is_active' => true,
        ]);
        $collaborator->locations()->sync([$location->id]);
        $leadDoctor->locations()->sync([$location->id]);

        $report = DailyReport::factory()->create([
            'location_id' => $location->id,
            'status' => 'podnesen',
            'created_by_user_id' => $user->id,
            'submitted_by_user_id' => $user->id,
        ]);

        DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'service_id' => $hsc->id,
            'doctor_id' => $collaborator->id,
            'is_new_patient' => true,
            'item_price' => 100,
            'paid_amount' => 100,
            'remaining_amount' => 0,
            'payment_status' => 'placeno',
            'payment_method' => 'fiskalno',
            'entered_by_user_id' => $user->id,
        ]);

        DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'service_id' => $hsc->id,
            'doctor_id' => $collaborator->id,
            'is_new_patient' => false,
            'item_price' => 120,
            'paid_amount' => 120,
            'remaining_amount' => 0,
            'payment_status' => 'placeno',
            'payment_method' => 'fiskalno',
            'entered_by_user_id' => $user->id,
        ]);

        DailyReportItem::factory()->create([
            'daily_report_id' => $report->id,
            'service_id' => $iui->id,
            'doctor_id' => $leadDoctor->id,
            'is_new_patient' => true,
            'item_price' => 130,
            'paid_amount' => 130,
            'remaining_amount' => 0,
            'payment_status' => 'placeno',
            'payment_method' => 'fiskalno',
            'entered_by_user_id' => $user->id,
        ]);

        ReportConfiguration::query()->updateOrCreate(
            ['config_key' => 'daily_email_summary'],
            ['config_value' => [
                'service_ids' => [$hsc->id],
                'collaborator_ids' => [$collaborator->id],
                'lead_doctor_ids' => [$leadDoctor->id],
                'include_new_patients' => true,
            ]]
        );

        $summary = app(ReportSummaryService::class)->daily($report->fresh());

        $this->assertSame(3, $summary['services_count']);
        $this->assertSame(2, $summary['collaborators_total_count']);
        $this->assertSame(1, $summary['lead_doctors_total_count']);
        $this->assertSame(2, $summary['significant_services_total_count']);
        $this->assertSame(2, $summary['new_patients_count']);
    }
}
