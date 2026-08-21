<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database in strict dependency order.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            RequesterSeeder::class,
            VehicleSeeder::class,
            DriverSeeder::class,
            TripSeeder::class,
            FuelLogSeeder::class,
        ]);
    }
}
