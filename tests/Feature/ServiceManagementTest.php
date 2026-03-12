<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_service_category_and_service(): void
    {
        $admin = User::factory()->create([
            'role' => 'glavni_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('service-categories.store'), [
                'name' => 'IVF Pregledi',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect(route('service-categories.index'));

        $category = ServiceCategory::query()->where('name', 'IVF Pregledi')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('services.store'), [
                'service_category_id' => $category->id,
                'name' => 'Konsultacija IVF',
                'base_price' => '120.00',
                'code' => 'IVF-001',
                'description' => 'Prvi pregled i konsultacija',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect(route('services.index'));

        $this->assertDatabaseHas('services', [
            'name' => 'Konsultacija IVF',
            'service_category_id' => $category->id,
            'is_active' => true,
        ]);
    }

    public function test_nurse_cannot_access_services_module(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->get(route('services.index'))
            ->assertForbidden();
    }

    public function test_destroy_deactivates_service(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrator_klinike',
            'is_active' => true,
        ]);

        $service = Service::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('services.destroy', $service))
            ->assertRedirect(route('services.index'));

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'is_active' => false,
        ]);
    }
}
