<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function __construct(private readonly WarehouseService $warehouses)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('can:warehouses.view')->only(['index', 'show']);
        $this->middleware('can:warehouses.create')->only('store');
        $this->middleware('can:warehouses.update')->only('update');
        $this->middleware('can:warehouses.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        return $this->warehouses->paginate($request->only('search'), (int) $request->get('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        return $this->warehouses->create($data);
    }

    public function show(Warehouse $warehouse)
    {
        return $warehouse->load('products');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:50', 'unique:warehouses,code,'.$warehouse->id],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        return $this->warehouses->update($warehouse, $data);
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->warehouses->delete($warehouse);

        return response()->noContent();
    }
}
