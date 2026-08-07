<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->with('parent')
            ->withCount('products')
            ->search($filters['search'] ?? null)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            throw new \RuntimeException('Cannot delete a category that still has products assigned to it.');
        }

        $category->delete();
    }
}
