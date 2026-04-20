<?php

namespace Tests\Feature\Profile;

use Tests\TestCase;

class PreferencesControllerTest extends TestCase
{
    public function test_index_page()
    {
        $response = $this->get('/preferences');

        $response->assertStatus(200);
    }
}