<?php

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WarehouseService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Warehouse::query()
            ->search($filters['search'] ?? null)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Warehouse
    {
        return Warehouse::create($data);
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);

        return $warehouse->fresh();
    }

    public function delete(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }
}
