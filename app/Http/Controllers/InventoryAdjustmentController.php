<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\StoreInventoryAdjustmentRequest;
use App\Models\Product;
use App\Services\InventoryAdjustmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventoryAdjustmentController extends Controller
{
    public function __construct(private InventoryAdjustmentService $service) {}

    public function create()
    {
        Gate::authorize('inventory.adjust');
        $products = Product::where('active', true)->orderBy('name')->get();
        return view('inventory.adjustments.create', compact('products'));
    }

    public function store(StoreInventoryAdjustmentRequest $request)
    {
        $product = Product::findOrFail($request->product_id);
        
        if ($request->type === 'adjustment_in') {
            $this->service->increase($product, $request->quantity, $request->reason, $request->user());
        } else {
            $this->service->decrease($product, $request->quantity, $request->reason, $request->user());
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Ajuste de inventario aplicado exitosamente.');
    }
}
