<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_sees_landing_portal_with_admin_and_driver_login_options(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Portal de Acceso')
            ->assertSee('Portal Administrativo')
            ->assertSee('Ingresar como Administrador')
            ->assertSee('/admin/login')
            ->assertSee('Portal de Conductor')
            ->assertSee('Ingresar como Conductor')
            ->assertSee('/driver/login');
    }

    public function test_authenticated_admin_sees_quick_access_to_admin_panel(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Boss']);
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->get('/');

        $response->assertOk()
            ->assertSee('Sesión activa como: Admin Boss')
            ->assertSee('Ir al Panel Admin')
            ->assertSee(url('/admin'));
    }

    public function test_authenticated_driver_sees_quick_access_to_driver_panel(): void
    {
        $driverUser = User::factory()->create(['name' => 'Carlos Chofer']);
        $driverUser->assignRole('driver');
        Driver::factory()->active()->create(['user_id' => $driverUser->id]);

        $response = $this->actingAs($driverUser)->get('/');

        $response->assertOk()
            ->assertSee('Sesión activa como: Carlos Chofer')
            ->assertSee('Ir al Panel Conductor')
            ->assertSee(url('/driver'));
    }
}
