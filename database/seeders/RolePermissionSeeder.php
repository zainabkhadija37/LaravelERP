<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'products', 'categories', 'warehouses', 'suppliers', 'customers',
            'purchase-orders', 'sales', 'stock-adjustments',
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }

        // Extra one-off permissions that don't follow the CRUD pattern.
        foreach ([
            'products.export',
            'purchase-orders.approve',
            'purchase-orders.receive',
            'purchase-orders.cancel',
            'sales.complete',
            'sales.cancel',
            'reports.view',
            'activity-log.view',
        ] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions(Permission::whereNotIn('name', [
            // Managers can run the business day-to-day but not delete master data
            // or manage users/roles — that stays with Admin.
            'categories.delete', 'warehouses.delete', 'suppliers.delete', 'customers.delete',
        ])->get());

        $employee = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $employee->syncPermissions(Permission::whereIn('name', [
            'products.view', 'categories.view', 'warehouses.view',
            'suppliers.view', 'customers.view', 'customers.create', 'customers.update',
            'sales.view', 'sales.create', 'sales.complete',
            'purchase-orders.view',
            'stock-adjustments.view', 'stock-adjustments.create',
        ])->get());
    }
}
