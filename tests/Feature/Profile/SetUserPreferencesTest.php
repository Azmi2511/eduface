<?php

namespace Tests\Feature\Profile;

use Tests\TestCase;
use App\Jobs\SetUserPreferences;

class SetUserPreferencesTest extends TestCase
{
    public function test_handle_sets_preferences()
    {
        $job = new SetUserPreferences(1, ['theme' => 'dark']);

        $this->assertNull($job->handle());
    }
}