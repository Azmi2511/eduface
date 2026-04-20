<?php
namespace Tests\Feature\Profile;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferenceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_update_their_preferences()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/preferences', [
            'theme' => 'dark',
            'language' => 'id'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'key' => 'theme',
            'value' => 'dark'
        ]);
    }

    /** @test */
    public function middleware_sets_user_preferences_successfully()
    {
        $user = User::factory()->create();
        // Simulasi setting bahasa di database
        UserPreference::create(['user_id' => $user->id, 'key' => 'language', 'value' => 'en']);

        $this->actingAs($user)->get('/');

        $this->assertEquals('en', app()->getLocale());
    }
}