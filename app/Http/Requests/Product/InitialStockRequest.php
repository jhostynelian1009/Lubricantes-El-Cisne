<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use App\Services\DataNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class InitialStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');
        return $product && ($this->user()?->can('initialStock', $product) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reason' => DataNormalizer::string($this->input('reason')) ?? 'Carga inicial de inventario',
        ]);
    }

    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'regex:/^\d+(\.\d{1,3})?$/',
                function ($attribute, $value, $fail) use ($product) {
                    if (!$product->active) {
                        $fail('No se puede realizar la carga inicial en un producto inactivo.');
                    }
                    if ((float) $product->current_stock > 0 || $product->hasMovements()) {
                        $fail('La carga inicial de inventario solo se puede ejecutar una vez y cuando el stock es cero.');
                    }
                },
            ],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'La cantidad inicial es obligatoria.',
            'quantity.numeric' => 'La cantidad inicial debe ser un número.',
            'quantity.gt' => 'La cantidad inicial debe ser mayor que cero.',
            'quantity.regex' => 'La cantidad inicial no puede tener más de 3 decimales.',
            'unit_cost.numeric' => 'El costo unitario debe ser un número.',
            'unit_cost.min' => 'El costo unitario no puede ser negativo.',
            'reason.max' => 'El motivo no puede exceder los 500 caracteres.',
        ];
    }
}
