<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

class AuditLogPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Audit Log Permissions
        $auditPermissions = [
            ['name' => 'view audit logs', 'guard_name' => 'web'],
            ['name' => 'view audit log details', 'guard_name' => 'web'],
        ];

        $auditPermissionMap = [];
        foreach ($auditPermissions as $permissionData) {
            $permission = Permission::firstOrCreate($permissionData);
            $auditPermissionMap[$permissionData['name']] = $permission->id;
        }

        // Assign audit log permissions to Super Admin role
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            // Get all permissions first
            $allPermissions = Permission::all();
            $superAdmin->syncPermissions($allPermissions->pluck('id'));
        }
    }
}