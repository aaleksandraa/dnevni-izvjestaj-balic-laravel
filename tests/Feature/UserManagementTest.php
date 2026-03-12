<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_with_permissions_and_locations(): void
    {
        $admin = User::factory()->create([
            'role' => 'glavni_admin',
            'is_active' => true,
        ]);

        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Sestra A',
                'email' => 'sestra.a@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'medicinska_sestra',
                'phone' => '061000111',
                'is_active' => '1',
                'can_submit_report' => '1',
                'can_change_submitter' => '0',
                'location_ids' => [$locationA->id, $locationB->id],
            ]);

        $response->assertRedirect(route('users.index'));

        $created = User::query()->where('email', 'sestra.a@example.com')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'id' => $created->id,
            'role' => 'medicinska_sestra',
            'is_active' => true,
            'can_submit_report' => true,
            'can_change_submitter' => false,
        ]);

        $this->assertDatabaseHas('user_location', [
            'user_id' => $created->id,
            'location_id' => $locationA->id,
        ]);
        $this->assertDatabaseHas('user_location', [
            'user_id' => $created->id,
            'location_id' => $locationB->id,
        ]);
    }

    public function test_nurse_cannot_access_user_management(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_clinic_admin_cannot_create_other_admin_roles(): void
    {
        $clinicAdmin = User::factory()->create([
            'role' => 'administrator_klinike',
            'is_active' => true,
        ]);
        $location = Location::factory()->create();

        $response = $this->actingAs($clinicAdmin)
            ->post(route('users.store'), [
                'name' => 'Novi Admin',
                'email' => 'novi.admin@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'administrator_klinike',
                'location_ids' => [$location->id],
                'is_active' => '1',
                'can_submit_report' => '1',
            ]);

        $response->assertSessionHasErrors(['role']);

        $this->assertDatabaseMissing('users', [
            'email' => 'novi.admin@example.com',
        ]);
    }
}
