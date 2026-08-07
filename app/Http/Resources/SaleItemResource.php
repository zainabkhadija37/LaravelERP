<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->whenLoaded('product', fn () => $this->product->name),
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'discount' => (float) $this->discount,
            'tax_rate' => (float) $this->tax_rate,
            'line_total' => (float) $this->line_total,
        ];
    }
}
