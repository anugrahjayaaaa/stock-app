<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // Roles
            ['name' => 'view roles', 'guard_name' => 'web'],
            ['name' => 'create roles', 'guard_name' => 'web'],
            ['name' => 'edit roles', 'guard_name' => 'web'],
            ['name' => 'delete roles', 'guard_name' => 'web'],

            // Permissions
            ['name' => 'view permissions', 'guard_name' => 'web'],
            ['name' => 'create permissions', 'guard_name' => 'web'],
            ['name' => 'edit permissions', 'guard_name' => 'web'],
            ['name' => 'delete permissions', 'guard_name' => 'web'],

            // Audit Logs
            ['name' => 'view audit logs', 'guard_name' => 'web'],
            ['name' => 'view audit log details', 'guard_name' => 'web'],

            // Stock features
            ['name' => 'view stock charts', 'guard_name' => 'web'],
            ['name' => 'view brokers', 'guard_name' => 'web'],
            ['name' => 'view stock analysis', 'guard_name' => 'web'],
        ];

        $permissionMap = [];
        foreach ($permissions as $permissionData) {
            $permission = Permission::firstOrCreate($permissionData);
            $permissionMap[$permissionData['name']] = $permission->id;
        }

        // Create Roles
        $roles = [
            ['name' => 'Super Admin'],
            ['name' => 'Admin'],
            ['name' => 'User'],
            ['name' => 'Admin Staff'],
            ['name' => 'Editor'],
            ['name' => 'Auditor'],
        ];

        $roleMap = [];
        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate($roleData);
            $roleMap[$roleData['name']] = $role->id;
            $role->syncPermissions(collect($permissions)->pluck('name')->all());
        }
    }
}
