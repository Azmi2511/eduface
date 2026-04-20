<?php

namespace Tests\Unit\Permission;

use Tests\TestCase;
use App\Models\Permission;

class PermissionModelTest extends TestCase
{
    public function test_model()
    {
        $model = new Permission(['status' => 'pending']);
        $this->assertEquals('pending', $model->status);
    }
}