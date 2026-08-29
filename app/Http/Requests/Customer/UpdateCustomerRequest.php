<?php

namespace App\Http\Requests\Customer;

use App\Services\DataNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');
        return $customer && ($this->user()?->can('update', $customer) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => DataNormalizer::string($this->input('name')),
            'identification' => DataNormalizer::code($this->input('identification')),
            'phone' => DataNormalizer::code($this->input('phone')),
            'email' => DataNormalizer::email($this->input('email')),
            'address' => DataNormalizer::string($this->input('address')),
        ]);

        $this->request->remove('active');
    }

    public function rules(): array
    {
        $customer = $this->route('customer');
        $customerId = $customer instanceof \App\Models\Customer ? $customer->id : $customer;

        return [
            'name' => ['required', 'string', 'max:160'],
            'identification' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'identification')->ignore($customerId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del cliente es obligatorio.',
            'name.max' => 'El nombre del cliente no debe exceder los 160 caracteres.',
            'identification.max' => 'La identificación no debe exceder los 20 caracteres.',
            'identification.unique' => 'Ya existe un cliente registrado con esta identificación.',
            'phone.max' => 'El teléfono no debe exceder los 30 caracteres.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.max' => 'El correo electrónico no debe exceder los 190 caracteres.',
            'address.max' => 'La dirección no debe exceder los 255 caracteres.',
        ];
    }
}
