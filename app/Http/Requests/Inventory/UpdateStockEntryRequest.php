<?php

namespace App\Http\Requests\Inventory;

class UpdateStockEntryRequest extends StoreStockEntryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('stock_entry')) ?? false;
    }
}
