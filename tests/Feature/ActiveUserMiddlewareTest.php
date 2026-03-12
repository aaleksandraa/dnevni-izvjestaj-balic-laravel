<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveUserMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_is_redirected_to_login(): void
    {
        $inactiveUser = User::factory()->create([
            'is_active' => false,
        ]);

        $this->actingAs($inactiveUser)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}
