<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogViewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_filter_audit_logs(): void
    {
        $admin = User::factory()->create([
            'role' => 'glavni_admin',
            'is_active' => true,
            'name' => 'Glavni',
            'email' => 'glavni@example.com',
        ]);

        $otherUser = User::factory()->create([
            'role' => 'administrator_klinike',
            'is_active' => true,
            'name' => 'Admin Klinike',
            'email' => 'klinika@example.com',
        ]);

        AuditLog::query()->create([
            'user_id' => $admin->id,
            'entity_type' => 'users',
            'entity_id' => 10,
            'action' => 'updated',
            'old_values' => ['name' => 'Old'],
            'new_values' => ['name' => 'New'],
            'description' => 'Azuriran korisnik old@example.com',
        ]);

        AuditLog::query()->create([
            'user_id' => $otherUser->id,
            'entity_type' => 'daily_report_items',
            'entity_id' => 25,
            'action' => 'created',
            'old_values' => null,
            'new_values' => ['item_price' => 100],
            'description' => 'Dodana stavka usluge',
        ]);

        $this->actingAs($admin)
            ->get(route('audit-logs.index', [
                'entity_type' => 'users',
                'action' => 'updated',
                'user_id' => $admin->id,
            ]))
            ->assertOk()
            ->assertSee('Azuriran korisnik old@example.com')
            ->assertDontSee('Dodana stavka usluge');
    }

    public function test_nurse_cannot_access_audit_logs(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->get(route('audit-logs.index'))
            ->assertForbidden();
    }
}
