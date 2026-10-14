<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Dandan Sampah Brebes');
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Login Admin');
    }

    public function test_admin_can_authenticate_using_valid_credentials(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@dandan.test',
            'role' => 'admin',
            'password' => bcrypt('admin123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@dandan.test',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@dandan.test',
            'role' => 'admin',
            'password' => bcrypt('admin123'),
        ]);

        $this->post('/login', [
            'email' => 'admin@dandan.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_dashboard_when_authenticated(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@dandan.test',
            'role' => 'admin',
            'password' => bcrypt('admin123'),
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dasbor Administrator');
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@dandan.test',
            'role' => 'admin',
            'password' => bcrypt('admin123'),
        ]);

        $response = $this->actingAs($admin)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
