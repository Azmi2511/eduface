<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class AuthControllerApiTest extends TestCase
{
    public function test_api_login_endpoint_exists()
    {
        $response = $this->post('/api/v1/login', []);

        $this->assertNotEquals(404, $response->getStatusCode());
    }

    public function test_api_logout_endpoint_exists()
    {
        $response = $this->post('/api/v1/logout');

        $this->assertNotEquals(404, $response->getStatusCode());
    }
}