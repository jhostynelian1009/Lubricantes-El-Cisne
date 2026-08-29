<?php

namespace App\Http\Requests\Category;

use App\Services\DataNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Category::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => DataNormalizer::string($this->input('name')),
            'description' => DataNormalizer::string($this->input('description')),
        ]);

        // Explicitly remove active if submitted in form payload
        $this->request->remove('active');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.max' => 'El nombre de la categoría no debe exceder los 100 caracteres.',
            'name.unique' => 'Ya existe una categoría registrada con este nombre.',
            'description.max' => 'La descripción no debe exceder los 1000 caracteres.',
        ];
    }
}
