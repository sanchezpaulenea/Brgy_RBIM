<?php

namespace Tests\Feature\Authentication;

use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Tests\TestCase;

class StatefulApiSessionTest extends TestCase
{
    public function test_the_host_that_received_the_request_is_stateful(): void
    {
        $request = Request::create('http://192.0.2.10:8000/api/v1/auth/login', 'POST');
        $request->headers->set('referer', 'http://192.0.2.10:8000/login');

        $this->assertTrue(EnsureFrontendRequestsAreStateful::fromFrontend($request));
    }

    public function test_a_foreign_origin_does_not_receive_a_session(): void
    {
        $request = Request::create('http://127.0.0.1:8000/api/v1/auth/login', 'POST');
        $request->headers->set('origin', 'https://evil.example');

        $this->assertFalse(EnsureFrontendRequestsAreStateful::fromFrontend($request));
    }
}
