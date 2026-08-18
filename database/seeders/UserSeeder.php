<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@carfleet.test'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['super_admin']);

        // 2. Driver User
        $driver = User::firstOrCreate(
            ['email' => 'driver@carfleet.test'],
            [
                'name' => 'Conductor de Prueba',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $driver->syncRoles(['driver']);
    }
}
