<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Illustrative Policy class. In this project most authorization is handled
 * via Spatie's permission-based Gate checks (`can:products.view` middleware),
 * since permissions here are simple CRUD flags rather than per-record rules.
 * This policy is wired up for the one case that IS per-record: an Employee
 * may only update a product they don't own suppliers for... (extend as needed).
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('products.view');
    }

    public function create(User $user): bool
    {
        return $user->can('products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('products.update');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('products.delete');
    }
}
