<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('inventory.adjust') ?? false;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:adjustment_in,adjustment_out'],
            'quantity' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,3})?$/'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.regex' => 'La cantidad debe tener máximo 3 decimales y no usar notación científica.',
        ];
    }
}
