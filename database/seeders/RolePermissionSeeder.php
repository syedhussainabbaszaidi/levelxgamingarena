<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permissions
        $permissions = [];
        foreach (Permission::CATALOG as $slug => [$name, $group]) {
            $permissions[$slug] = Permission::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'group' => $group]
            );
        }

        // 2. Roles + their permission matrix
        $roles = [
            Role::SUPER_ADMIN => ['Super Admin', 'Unrestricted access including roles and system settings.', true],
            Role::ADMIN => ['Admin', 'Full operational access to bookings, activities, users and settings.', true],
            Role::BOOKING_MANAGER => ['Booking Manager', 'Manages bookings, activities, pricing and customer records.', true],
            Role::STAFF => ['Staff', 'Front-desk booking and customer handling.', true],
        ];

        foreach ($roles as $slug => [$name, $description, $system]) {
            $role = Role::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'is_system' => $system]
            );

            $granted = Role::MATRIX[$slug] === '*'
                ? array_keys(Permission::CATALOG)
                : Role::MATRIX[$slug];

            $role->permissions()->sync(
                collect($granted)->map(fn (string $s) => $permissions[$s]->id)->all()
            );
        }
    }
}
