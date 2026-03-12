<?php

namespace Tests\Feature;

use App\Models\ReportConfiguration;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_settings_and_save_daily_email_summary_configuration(): void
    {
        $admin = User::factory()->create([
            'role' => 'glavni_admin',
            'is_active' => true,
        ]);

        $service = Service::factory()->create([
            'service_category_id' => ServiceCategory::factory()->create()->id,
            'is_active' => true,
        ]);
        $collaborator = StaffMember::factory()->create([
            'role_type' => 'saradnik',
            'is_active' => true,
        ]);
        $leadDoctor = StaffMember::factory()->create([
            'role_type' => 'primarni_doktor',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Podesavanja')
            ->assertSee('Medicinski tim')
            ->assertSee('Korisnici')
            ->assertSee('Email primaoci')
            ->assertSee('Audit log');

        $this->actingAs($admin)
            ->put(route('settings.daily-email-summary.update'), [
                'service_ids' => [$service->id],
                'collaborator_ids' => [$collaborator->id],
                'lead_doctor_ids' => [$leadDoctor->id],
                'include_new_patients' => '1',
            ])
            ->assertRedirect(route('settings.daily-email-summary.edit'));

        /** @var ReportConfiguration $configuration */
        $configuration = ReportConfiguration::query()
            ->where('config_key', 'daily_email_summary')
            ->firstOrFail();

        $this->assertSame([$service->id], $configuration->config_value['service_ids']);
        $this->assertSame([$collaborator->id], $configuration->config_value['collaborator_ids']);
        $this->assertSame([$leadDoctor->id], $configuration->config_value['lead_doctor_ids']);
        $this->assertTrue((bool) $configuration->config_value['include_new_patients']);
    }

    public function test_nurse_cannot_access_settings_module(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->get(route('settings.index'))
            ->assertForbidden();

        $this->actingAs($nurse)
            ->get(route('settings.daily-email-summary.edit'))
            ->assertForbidden();
    }
}
