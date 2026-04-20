<?php
namespace Tests\Unit\Security;

use Tests\TestCase;
use App\Services\JwtService;

class JwtServiceTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_valid_jwt_token()
    {
        $service = new JwtService();
        $payload = ['user_id' => 1, 'role' => 'admin'];

        $token = $service->generate($payload);
        $this->assertIsString($token);

        $decoded = $service->verify($token);
        $this->assertIsArray($decoded);
        $this->assertEquals(1, $decoded['user_id']);
        $this->assertEquals('admin', $decoded['role']);
    }

}