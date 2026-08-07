<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->search($filters['search'] ?? null)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->fresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
