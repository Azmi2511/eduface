<?php
namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_login_page()
    {
        $this->get(route('login.show'))->assertStatus(200);
    }

    /** @test */
    public function user_can_login_via_web()
    {
        $user = User::factory()->create(['password' => bcrypt($pwd = 'secret123')]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => $pwd,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_can_login_via_api()
    {
        $user = User::factory()->create(['password' => bcrypt($pwd = 'secret123')]);

        $response = $this->postJson('/api/v1/login', [
            'username' => $user->username,
            'password' => $pwd,
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }
}