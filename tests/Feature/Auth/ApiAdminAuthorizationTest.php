<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_api_user_cannot_use_admin_routes_with_an_admin_session_role(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        Sanctum::actingAs($teacher);
        $this->withSession(['role' => 'admin']);

        $this->getJson('/api/v1/users')->assertForbidden();
        $this->putJson('/api/v1/settings/general', [
            'school_name' => 'Unauthorized update',
        ])->assertForbidden();
    }
}
