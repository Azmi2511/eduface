<?php
namespace Tests\Feature\Permission;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_update_permission_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $permission = Permission::factory()->create(['approval_status' => 'Pending']);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/v1/permissions/{$permission->id}/status", [
            'status' => 'Approved'
        ]);

        $response->assertOk();
        $this->assertEquals('Approved', $permission->fresh()->approval_status);
    }
}