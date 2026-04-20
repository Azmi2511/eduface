<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;
use Illuminate\Http\Request;
use App\Http\Middleware\SessionAuth;
use Illuminate\Http\Response;

class SessionAuthTest extends TestCase
{
    public function test_handle_redirects_when_not_logged_in()
    {
        $middleware = new SessionAuth();

        $request = Request::create('/dashboard', 'GET');

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_handle_allows_when_logged_in()
    {
        session(['user_id' => 1]);

        $middleware = new SessionAuth();

        $request = Request::create('/dashboard', 'GET');

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }
}