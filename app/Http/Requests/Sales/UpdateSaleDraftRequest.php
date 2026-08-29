<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sale = $this->route('sale');
        return $sale && ($this->user()?->can('update', $sale) ?? false);
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'details' => ['present', 'array', 'max:50'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.quantity' => ['required', 'string', 'regex:/^\d+(\.\d{1,3})?$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'details.max' => 'Una venta no puede contener más de 50 productos distintos.',
            'details.*.quantity.regex' => 'La cantidad debe ser un número entero o decimal con hasta 3 decimales.',
        ];
    }
}
