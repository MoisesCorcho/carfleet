<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Base Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $driverRole = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);

        // 2. Base Entity Permissions
        $entities = ['Role', 'Vehicle', 'Driver'];
        $prefixes = [
            'ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny',
            'Restore', 'ForceDelete', 'ForceDeleteAny', 'RestoreAny', 'Replicate', 'Reorder',
        ];

        foreach ($entities as $entity) {
            foreach ($prefixes as $prefix) {
                Permission::firstOrCreate([
                    'name' => "{$prefix}:{$entity}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // 3. Sync all permissions to super_admin and admin
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);
        $adminRole->syncPermissions($allPermissions);
    }
}
