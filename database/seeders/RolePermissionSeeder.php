<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User management permissions
            ['name' => 'user.view', 'display_name' => 'View Users', 'display_name_kh' => 'មើលអ្នកប្រើប្រាស់', 'action' => 'view', 'subject' => 'user'],
            ['name' => 'user.create', 'display_name' => 'Create Users', 'display_name_kh' => 'បង្កើតអ្នកប្រើប្រាស់', 'action' => 'create', 'subject' => 'user'],
            ['name' => 'user.update', 'display_name' => 'Update Users', 'display_name_kh' => 'ធ្វើបច្ចុប្បន្នភាពអ្នកប្រើប្រាស់', 'action' => 'update', 'subject' => 'user'],
            ['name' => 'user.delete', 'display_name' => 'Delete Users', 'display_name_kh' => 'លុបអ្នកប្រើប្រាស់', 'action' => 'delete', 'subject' => 'user'],

            // Role management permissions
            ['name' => 'role.view', 'display_name' => 'View Roles', 'display_name_kh' => 'មើលតួនាទី', 'action' => 'view', 'subject' => 'role'],
            ['name' => 'role.create', 'display_name' => 'Create Roles', 'display_name_kh' => 'បង្កើតតួនាទី', 'action' => 'create', 'subject' => 'role'],
            ['name' => 'role.update', 'display_name' => 'Update Roles', 'display_name_kh' => 'ធ្វើបច្ចុប្បន្នភាពតួនាទី', 'action' => 'update', 'subject' => 'role'],
            ['name' => 'role.delete', 'display_name' => 'Delete Roles', 'display_name_kh' => 'លុបតួនាទី', 'action' => 'delete', 'subject' => 'role'],

            // Permission management permissions
            ['name' => 'permission.view', 'display_name' => 'View Permissions', 'display_name_kh' => 'មើលការអនុញ្ញាត', 'action' => 'view', 'subject' => 'permission'],
            ['name' => 'permission.create', 'display_name' => 'Create Permissions', 'display_name_kh' => 'បង្កើតការអនុញ្ញាត', 'action' => 'create', 'subject' => 'permission'],
            ['name' => 'permission.update', 'display_name' => 'Update Permissions', 'display_name_kh' => 'ធ្វើបច្ចុប្បន្នភាពការអនុញ្ញាត', 'action' => 'update', 'subject' => 'permission'],
            ['name' => 'permission.delete', 'display_name' => 'Delete Permissions', 'display_name_kh' => 'លុបការអនុញ្ញាត', 'action' => 'delete', 'subject' => 'permission'],

            // System management permissions
            ['name' => 'system.admin', 'display_name' => 'System Administration', 'display_name_kh' => 'ការគ្រប់គ្រងប្រព័ន្ធ', 'action' => 'admin', 'subject' => 'system'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }

        // Create roles
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'display_name_kh' => 'អ្នកគ្រប់គ្រងជាន់ខ្ពស់',
                'restricted' => true,
                'permissions' => Permission::all()->pluck('id')->toArray(),
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'display_name_kh' => 'អ្នកគ្រប់គ្រង',
                'restricted' => false,
                'permissions' => Permission::whereIn('name', [
                    'user.view',
                    'user.create',
                    'user.update',
                    'role.view',
                    'role.create',
                    'role.update',
                    'permission.view',
                ])->pluck('id')->toArray(),
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'display_name_kh' => 'អ្នកប្រើប្រាស់',
                'restricted' => false,
                'permissions' => Permission::whereIn('name', [
                    'user.view',
                ])->pluck('id')->toArray(),
            ],
        ];

        foreach ($roles as $roleData) {
            $permissionIds = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::create($roleData);
            $role->assignPermissions($permissionIds);
        }

        // Assign super admin role to first user if exists
        $firstUser = User::first();
        if ($firstUser) {
            $superAdminRole = Role::where('name', 'super_admin')->first();
            if ($superAdminRole) {
                $firstUser->assignRole($superAdminRole);
            }
        }
    }
}
