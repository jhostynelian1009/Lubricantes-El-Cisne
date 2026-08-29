<?php

namespace App\Http\Requests\Product;

use App\Models\Category;
use App\Models\Product;
use App\Services\DataNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Product::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $rawSku = DataNormalizer::string($this->input('sku'));
        $cleanSku = $rawSku ? strtoupper(str_replace(' ', '', $rawSku)) : null;

        $this->merge([
            'sku' => $cleanSku,
            'barcode' => DataNormalizer::code($this->input('barcode')),
            'name' => DataNormalizer::string($this->input('name')),
            'description' => DataNormalizer::string($this->input('description')),
            'unit' => DataNormalizer::string($this->input('unit')),
            'minimum_stock' => $this->input('minimum_stock') !== null && $this->input('minimum_stock') !== '' ? $this->input('minimum_stock') : '0.000',
            'last_cost' => $this->input('last_cost') !== null && $this->input('last_cost') !== '' ? $this->input('last_cost') : '0.00',
        ]);

        $this->request->remove('current_stock');
    }

    public function rules(): array
    {
        $allowedUnits = array_keys(config('inventory.units', []));

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('active', true);
                }),
            ],
            'sku' => ['required', 'string', 'max:60', Rule::unique('products', 'sku')],
            'barcode' => ['nullable', 'string', 'max:80', Rule::unique('products', 'barcode')],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit' => ['required', 'string', Rule::in($allowedUnits)],
            'minimum_stock' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,3})?$/'],
            'last_cost' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.exists' => 'La categoría seleccionada no existe o no está activa.',
            'sku.required' => 'El código SKU es obligatorio.',
            'sku.unique' => 'Ya existe un producto registrado con este código SKU.',
            'sku.max' => 'El código SKU no debe exceder los 60 caracteres.',
            'barcode.unique' => 'Ya existe un producto registrado con este código de barras.',
            'barcode.max' => 'El código de barras no debe exceder los 80 caracteres.',
            'name.required' => 'El nombre del producto es obligatorio.',
            'name.max' => 'El nombre del producto no debe exceder los 180 caracteres.',
            'unit.required' => 'La unidad de medida es obligatoria.',
            'unit.in' => 'La unidad de medida seleccionada no es válida.',
            'minimum_stock.required' => 'El stock mínimo es obligatorio.',
            'minimum_stock.numeric' => 'El stock mínimo debe ser un número.',
            'minimum_stock.min' => 'El stock mínimo no puede ser negativo.',
            'minimum_stock.regex' => 'El stock mínimo no puede tener más de 3 decimales.',
            'last_cost.numeric' => 'El costo referencial debe ser un número.',
            'last_cost.min' => 'El costo referencial no puede ser negativo.',
            'sale_price.required' => 'El precio de venta es obligatorio.',
            'sale_price.numeric' => 'El precio de venta debe ser un número.',
            'sale_price.gt' => 'El precio de venta debe ser mayor que cero.',
        ];
    }
}
