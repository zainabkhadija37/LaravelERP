<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('purchase-orders.create');
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
