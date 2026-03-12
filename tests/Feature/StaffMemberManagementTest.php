<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_staff_member_and_assign_locations(): void
    {
        $admin = User::factory()->create([
            'role' => 'glavni_admin',
            'is_active' => true,
        ]);

        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('staff-members.store'), [
                'full_name' => 'Dr Ana Markovic',
                'role_type' => 'primarni_doktor',
                'email' => 'ana@example.com',
                'phone' => '061111222',
                'internal_code' => 'DOC-001',
                'is_active' => '1',
                'location_ids' => [$locationA->id, $locationB->id],
            ]);

        $response->assertRedirect(route('staff-members.index'));

        $member = StaffMember::query()->where('internal_code', 'DOC-001')->firstOrFail();

        $this->assertDatabaseHas('staff_members', [
            'id' => $member->id,
            'full_name' => 'Dr Ana Markovic',
            'role_type' => 'primarni_doktor',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('location_staff', [
            'staff_member_id' => $member->id,
            'location_id' => $locationA->id,
        ]);
        $this->assertDatabaseHas('location_staff', [
            'staff_member_id' => $member->id,
            'location_id' => $locationB->id,
        ]);
    }

    public function test_nurse_cannot_access_staff_member_module(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->get(route('staff-members.index'))
            ->assertForbidden();
    }
}
