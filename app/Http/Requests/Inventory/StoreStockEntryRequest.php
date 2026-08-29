<?php

namespace App\Http\Requests\Inventory;

use App\Models\StockEntry;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', StockEntry::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date', 'before_or_equal:today'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'], // Se asume validación 'active' manualmente o ignorarla si es nullable, pero el requerimiento dice: "Si existe proveedor, debe estar activo."
            'reference' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'details' => ['required', 'array', 'min:1', 'max:200'],
            'details.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'], // Rechaza productos repetidos explícitamente.
            'details.*.quantity' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,3})?$/'], // admite máximo 3 decimales, rechaza notación científica (por regex)
            'details.*.unit_cost' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'], // admite máximo 2 decimales
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $supplierId = $this->input('supplier_id');
            if ($supplierId) {
                $supplier = \App\Models\Supplier::find($supplierId);
                if ($supplier && !$supplier->active) {
                    $validator->errors()->add('supplier_id', 'El proveedor seleccionado está inactivo.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'entry_date.before_or_equal' => 'La fecha no puede ser futura.',
            'details.max' => 'La entrada no puede tener más de 200 líneas.',
            'details.*.product_id.distinct' => 'Existen productos duplicados en la entrada.',
            'details.*.quantity.regex' => 'La cantidad debe tener un máximo de 3 decimales y no usar notación científica.',
            'details.*.unit_cost.regex' => 'El costo debe tener un máximo de 2 decimales y no usar notación científica.',
        ];
    }
}
