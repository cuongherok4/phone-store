<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminPermissions = [
            'admin.access',
            'products.manage',
            'orders.manage',
            'inventory.manage',
            'coupons.manage',
            'reports.view',
            'users.manage',
            'settings.manage',
            'banners.manage',
            'reviews.moderate',
        ];

        $customerPermissions = [
            'checkout.place',
            'orders.view-own',
            'orders.cancel-own',
            'reviews.create',
            'wishlist.manage',
        ];

        foreach (array_merge($adminPermissions, $customerPermissions) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate('admin', 'web')
            ->syncPermissions(array_merge($adminPermissions, $customerPermissions));

        Role::findOrCreate('customer', 'web')
            ->syncPermissions($customerPermissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
