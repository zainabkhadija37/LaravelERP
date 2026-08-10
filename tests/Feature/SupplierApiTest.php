<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Sanctum\Sanctum;

it('allows employees to create suppliers', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Employee');

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/v1/suppliers', [
        'name' => 'Acme Supplies',
        'company_name' => 'Acme Supplies Inc',
        'email' => 'sales@acme.test',
        'phone' => '123456',
        'address' => '123 Main St',
        'tax_number' => '12345',
        'is_active' => true,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('suppliers', ['name' => 'Acme Supplies']);
});
