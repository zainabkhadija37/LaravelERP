<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.update');
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'cost_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'selling_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'required', 'string', 'max:50'],
            'reorder_level' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
