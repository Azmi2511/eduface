<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;
use Illuminate\Http\Request;
use App\Http\Middleware\JwtMiddleware;

class JwtMiddlewareTest extends TestCase
{
    public function test_no_token_returns_unauthorized()
    {
        $middleware = new JwtMiddleware(app(\App\Services\JwtService::class));

        $request = Request::create('/api/v1/me', 'GET');

        $response = $middleware->handle($request, function () {
            return response()->json(['ok' => true]);
        });

        $this->assertEquals(401, $response->getStatusCode());
    }
}