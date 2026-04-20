<?php
namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_profile_edit_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/profile')->assertStatus(200);
    }

    /** @test */
    public function user_can_update_profile_information()
    {
        $user = User::factory()->create(['full_name' => 'Old Name']);
        
        $response = $this->actingAs($user)->patch('/profile', [
            'full_name' => 'New Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'full_name' => 'New Name']);
    }

    /** @test */
    public function user_can_update_password_via_api()
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        \Laravel\Sanctum\Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/me/password', [
            'current_password' => 'old-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
    }
}