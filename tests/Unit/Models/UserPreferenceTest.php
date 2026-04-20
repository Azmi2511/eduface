<?php
namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_retrieve_specific_preference_value()
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'key' => 'notifications',
            'value' => 'enabled'
        ]);

        $this->assertEquals('enabled', $user->getPref('notifications'));
    }

    /** @test */
    public function it_returns_default_if_preference_not_found()
    {
        $user = User::factory()->create();
        $this->assertEquals('default-value', $user->getPref('non_existent', 'default-value'));
    }
}