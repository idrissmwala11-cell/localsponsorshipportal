<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $route = Route::getRoutes()->match(Request::create('/'));
        $response = $route->run();

        $this->assertSame('/', $route->uri());
        $this->assertSame('welcome', $response->name());
    }
}
