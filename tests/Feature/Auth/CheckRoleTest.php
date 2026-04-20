<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckRole;

class CheckRoleTest extends TestCase
{
    public function test_handle_forbidden_if_role_not_allowed()
    {
        $middleware = new CheckRole();

        $request = Request::create('/admin', 'GET');
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(function () {
            return (object)['role' => 'student'];
        });

        $response = $middleware->handle($request, function () {
            return response('OK');
        }, 'admin');

        $this->assertEquals(403, $response->getStatusCode());
    }
}