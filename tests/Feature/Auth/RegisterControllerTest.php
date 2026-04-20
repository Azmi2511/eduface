<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    public function test_register_page()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_send_otp_endpoint()
    {
        $response = $this->post('/register/send-otp', []);
        $this->assertNotEquals(404, $response->getStatusCode());
    }
}