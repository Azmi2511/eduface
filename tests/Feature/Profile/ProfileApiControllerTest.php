<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiControllerTest extends TestCase
{
    public function test_profile_api_exists()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->get('/api/v1/me');
        $this->assertNotEquals(404, $response->getStatusCode());
    }
}