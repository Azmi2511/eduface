<?php

namespace Tests\Unit\Permission;

use Tests\TestCase;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;

class PermissionResourceTest extends TestCase
{
    public function test_resource_array()
    {
        $permission = new \App\Models\Permission([
            'id' => 1,
            'status' => 'pending'
        ]);

        $resource = new PermissionResource($permission);

        $array = $resource->toArray(new Request());

        $this->assertArrayHasKey('status', $array);
    }
}