<?php

namespace Tests\Feature\Profile;

use Tests\TestCase;
use App\Models\UserPreference;

class UserPreferenceModelTest extends TestCase
{
    public function test_model_instantiation()
    {
        $model = new UserPreference([
            'user_id' => 1,
            'key' => 'theme',
            'value' => 'dark'
        ]);

        $this->assertEquals('theme', $model->key);
    }
}