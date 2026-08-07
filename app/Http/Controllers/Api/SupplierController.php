<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $suppliers)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('can:suppliers.view')->only(['index', 'show']);
        $this->middleware('can:suppliers.create')->only('store');
        $this->middleware('can:suppliers.update')->only('update');
        $this->middleware('can:suppliers.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        return $this->suppliers->paginate($request->only('search'), (int) $request->get('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        return $this->suppliers->create($data);
    }

    public function show(Supplier $supplier)
    {
        return $supplier->load('products');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        return $this->suppliers->update($supplier, $data);
    }

    public function destroy(Supplier $supplier)
    {
        $this->suppliers->delete($supplier);

        return response()->noContent();
    }
}
