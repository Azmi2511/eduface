<?php

namespace Tests\Feature\Auth;

use App\Services\JwtService;
use Tests\TestCase;

class JwtHelperTest extends TestCase
{
    public function test_jwt_token_generation_and_verification()
    {
        $service = new JwtService();
        $payload = ['user_id' => 42, 'role' => 'teacher'];

        $token = $service->generate($payload);
        $this->assertIsString($token);
        $this->assertMatchesRegularExpression('/^[^.]+\.[^.]+\.[^.]+$/', $token);

        $decoded = $service->verify($token);
        $this->assertIsArray($decoded);
        $this->assertEquals(42, $decoded['user_id']);
        $this->assertEquals('teacher', $decoded['role']);
    }
}