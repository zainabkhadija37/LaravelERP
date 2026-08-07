<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customers)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('can:customers.view')->only(['index', 'show']);
        $this->middleware('can:customers.create')->only('store');
        $this->middleware('can:customers.update')->only('update');
        $this->middleware('can:customers.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        return $this->customers->paginate($request->only('search'), (int) $request->get('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        return $this->customers->create($data);
    }

    public function show(Customer $customer)
    {
        return $customer->load(['sales' => fn ($q) => $q->latest()->limit(10)]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        return $this->customers->update($customer, $data);
    }

    public function destroy(Customer $customer)
    {
        $this->customers->delete($customer);

        return response()->noContent();
    }
}
