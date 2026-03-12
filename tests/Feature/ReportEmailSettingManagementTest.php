<?php

namespace Tests\Feature;

use App\Models\ReportEmailSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportEmailSettingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_report_email_setting(): void
    {
        $admin = User::factory()->create([
            'role' => 'glavni_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('report-email-settings.store'), [
                'report_type' => 'daily',
                'email' => 'daily.team@example.com',
                'is_active' => '1',
            ])
            ->assertRedirect(route('report-email-settings.index'));

        $this->assertDatabaseHas('report_email_settings', [
            'report_type' => 'daily',
            'email' => 'daily.team@example.com',
            'is_active' => true,
        ]);
    }

    public function test_nurse_cannot_access_report_email_settings_module(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->get(route('report-email-settings.index'))
            ->assertForbidden();
    }

    public function test_duplicate_email_for_same_report_type_is_rejected(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrator_klinike',
            'is_active' => true,
        ]);

        ReportEmailSetting::query()->create([
            'report_type' => 'weekly',
            'email' => 'manager@example.com',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('report-email-settings.store'), [
                'report_type' => 'weekly',
                'email' => 'manager@example.com',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors(['email']);
    }

    public function test_destroy_action_deactivates_setting(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrator_klinike',
            'is_active' => true,
        ]);

        $setting = ReportEmailSetting::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('report-email-settings.destroy', $setting))
            ->assertRedirect(route('report-email-settings.index'));

        $this->assertDatabaseHas('report_email_settings', [
            'id' => $setting->id,
            'is_active' => false,
        ]);
    }
}
