<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@erp.test',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('Admin');

        $manager = User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@erp.test',
            'password' => bcrypt('password'),
        ]);
        $manager->assignRole('Manager');

        $employee = User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@erp.test',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('Employee');

        $this->call([
            DemoDataSeeder::class,
        ]);
    }
}
