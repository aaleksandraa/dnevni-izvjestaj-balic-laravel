<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_locations_index_and_create_location(): void
    {
        $admin = User::factory()->create([
            'role' => 'glavni_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('locations.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('locations.store'), [
                'name' => 'IVF Centar Sarajevo',
                'address' => 'Ulica 1',
                'city' => 'Sarajevo',
                'phone' => '+38761111222',
                'email' => 'sarajevo@ivf.local',
                'is_active' => '1',
                'notes' => 'Prva lokacija',
            ])
            ->assertRedirect(route('locations.index'));

        $this->assertDatabaseHas('locations', [
            'name' => 'IVF Centar Sarajevo',
            'city' => 'Sarajevo',
            'is_active' => true,
        ]);
    }

    public function test_regular_medical_staff_cannot_access_locations_module(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->get(route('locations.index'))
            ->assertForbidden();
    }

    public function test_deactivate_action_sets_location_to_inactive(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrator_klinike',
            'is_active' => true,
        ]);

        $location = Location::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('locations.destroy', $location))
            ->assertRedirect(route('locations.index'));

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'is_active' => false,
        ]);
    }
}
