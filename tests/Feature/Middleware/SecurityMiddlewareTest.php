<?php
namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Request;

class SecurityMiddlewareTest extends TestCase
{
    /** @test */
    public function check_role_middleware_blocks_unauthorized_role()
    {
        $user = User::factory()->make(['role' => 'student']);
        $this->actingAs($user);

        $middleware = new \App\Http\Middleware\CheckRole();
        $request = Request::create('/admin-only', 'GET');

        $response = $middleware->handle($request, function() {}, 'teacher');

        // Jika student akses area teacher, harusnya 403 Forbidden atau redirect
        $this->assertEquals(403, $response->getStatusCode());
    }
}