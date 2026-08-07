<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('can:categories.view')->only(['index', 'show']);
        $this->middleware('can:categories.create')->only('store');
        $this->middleware('can:categories.update')->only('update');
        $this->middleware('can:categories.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        return $this->categories->paginate($request->only('search'), (int) $request->get('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'is_active' => ['boolean'],
        ]);

        return $this->categories->create($data);
    }

    public function show(Category $category)
    {
        return $category->load('parent', 'children');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'is_active' => ['boolean'],
        ]);

        return $this->categories->update($category, $data);
    }

    public function destroy(Category $category)
    {
        $this->categories->delete($category);

        return response()->noContent();
    }
}
