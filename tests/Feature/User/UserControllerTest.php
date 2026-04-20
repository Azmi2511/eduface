<?php
namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_can_return_authenticated_user_data()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')
             ->assertOk()
             ->assertJson(['id' => $user->id]);
    }

    /** @test */
    public function user_can_update_fcm_token()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/user/update-fcm', ['fcm_token' => 'sample-token-123'])
             ->assertOk();

        $this->assertEquals('sample-token-123', $user->fresh()->fcm_token);
    }
}