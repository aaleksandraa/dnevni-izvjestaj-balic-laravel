<?php

namespace Tests\Feature;

use App\Models\Finding;
use App\Models\FindingCategory;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_finding_category_and_finding(): void
    {
        $admin = User::factory()->create([
            'role' => 'glavni_admin',
            'is_active' => true,
        ]);

        $service = Service::factory()->create();

        $this->actingAs($admin)
            ->post(route('finding-categories.store'), [
                'name' => 'Laboratorijski nalazi',
                'sort_order' => 2,
                'is_active' => '1',
            ])
            ->assertRedirect(route('finding-categories.index'));

        $category = FindingCategory::query()->where('name', 'Laboratorijski nalazi')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('findings.store'), [
                'finding_category_id' => $category->id,
                'service_id' => $service->id,
                'name' => 'Hormonski panel',
                'unit_price' => '80.00',
                'notes' => 'Standardni panel',
                'is_active' => '1',
            ])
            ->assertRedirect(route('findings.index'));

        $this->assertDatabaseHas('findings', [
            'name' => 'Hormonski panel',
            'finding_category_id' => $category->id,
            'service_id' => $service->id,
            'is_active' => true,
        ]);
    }

    public function test_nurse_cannot_access_findings_module(): void
    {
        $nurse = User::factory()->create([
            'role' => 'medicinska_sestra',
            'is_active' => true,
        ]);

        $this->actingAs($nurse)
            ->get(route('findings.index'))
            ->assertForbidden();
    }

    public function test_destroy_deactivates_finding(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrator_klinike',
            'is_active' => true,
        ]);

        $finding = Finding::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('findings.destroy', $finding))
            ->assertRedirect(route('findings.index'));

        $this->assertDatabaseHas('findings', [
            'id' => $finding->id,
            'is_active' => false,
        ]);
    }
}
