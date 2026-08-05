<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $permissions = [
            'view roles', 'create roles', 'edit roles', 'delete roles',
            'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);

        $superAdmin->givePermissionTo(Permission::all());
        $user->givePermissionTo(['view roles']);

        $super = User::firstOrCreate(
            ['email' => 'superadmin@stock.app'],
            ['name' => 'Super Admin', 'password' => bcrypt('password123')]
        );
        $super->assignRole('Super Admin');

        $usr = User::firstOrCreate(
            ['email' => 'user@stock.app'],
            ['name' => 'User', 'password' => bcrypt('password123')]
        );
        $usr->assignRole('User');
    }
}