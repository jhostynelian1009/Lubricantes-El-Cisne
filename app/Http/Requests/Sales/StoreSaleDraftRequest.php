<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Sale::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'details' => ['nullable', 'array', 'max:50'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.quantity' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,3})?$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'details.max' => 'Una venta no puede contener más de 50 productos distintos.',
            'details.*.quantity.gt' => 'La cantidad debe ser mayor que cero.',
            'details.*.quantity.regex' => 'La cantidad debe ser un número entero o decimal con hasta 3 decimales.',
        ];
    }
}
