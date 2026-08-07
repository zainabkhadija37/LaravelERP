<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('stock-adjustments.create');
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'type' => ['required', Rule::in(['increase', 'decrease'])],
            'reason' => ['required', Rule::in(['damaged', 'lost', 'found', 'correction', 'other'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
