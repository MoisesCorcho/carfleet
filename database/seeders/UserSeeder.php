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
        // 1. Super Admin Accounts
        $superAdminTest = User::firstOrCreate(
            ['email' => 'admin@carfleet.test'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdminTest->syncRoles(['super_admin']);

        $superAdminCom = User::firstOrCreate(
            ['email' => 'admin@carfleet.com'],
            [
                'name' => 'Administrador Principal',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdminCom->syncRoles(['super_admin']);

        // 2. Dispatcher / Fleet Manager
        $dispatcher = User::firstOrCreate(
            ['email' => 'despacho@carfleet.test'],
            [
                'name' => 'Coordinador de Despacho',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $dispatcher->syncRoles(['admin']);

        // 3. Driver User Accounts
        $driverAccounts = [
            [
                'email' => 'driver@carfleet.test',
                'name' => 'Carlos Andrés Rodríguez',
            ],
            [
                'email' => 'driver@carfleet.com',
                'name' => 'Carlos Andrés Rodríguez (Com)',
            ],
            [
                'email' => 'driver2@carfleet.test',
                'name' => 'Jorge Eliécer Gaitán',
            ],
            [
                'email' => 'driver3@carfleet.test',
                'name' => 'María Fernanda Gómez',
            ],
            [
                'email' => 'driver4@carfleet.test',
                'name' => 'Juan Pablo Montoya',
            ],
            [
                'email' => 'driver5@carfleet.test',
                'name' => 'Andrés Felipe Arias',
            ],
            [
                'email' => 'driver6@carfleet.test',
                'name' => 'Ricardo Arjona Morales',
            ],
        ];

        foreach ($driverAccounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles(['driver']);
        }
    }
}
