<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sale = $this->route('sale');
        return $sale && ($this->user()?->can('confirm', $sale) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
