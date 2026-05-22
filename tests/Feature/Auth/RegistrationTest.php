<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = app(RegisteredUserController::class)->create();

        $this->assertSame('auth.register', $response->name());
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'job_title' => 'Coordinator',
            'role' => 'user',
            'project_name' => 'compassion',
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('otp.verify', absolute: false));
    }
}
