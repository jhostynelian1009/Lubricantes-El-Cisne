<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('inventory.view');

        $query = InventoryMovement::with(['product', 'creator', 'reference'])->latest('id');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }
        if ($request->filled('reference_id')) {
            $query->where('reference_id', $request->reference_id);
        }

        $movements = $query->paginate(20)->withQueryString();
        
        $products = Product::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        
        $types = [
            'initial_adjustment' => 'Carga Inicial',
            'entry' => 'Entrada',
            'adjustment_in' => 'Ajuste (Incremento)',
            'adjustment_out' => 'Ajuste (Disminución)',
            // Future: 'sale', 'sale_reversal'
        ];

        return view('inventory.movements.index', compact('movements', 'products', 'users', 'types'));
    }

    public function kardex(Request $request)
    {
        Gate::authorize('inventory.view');

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        $initialMovement = InventoryMovement::where('product_id', $product->id)
            ->whereDate('created_at', '<', $request->start_date)
            ->latest('created_at')
            ->latest('id')
            ->first();
            
        $initialBalance = $initialMovement ? $initialMovement->quantity_after : '0.000';

        $movements = InventoryMovement::with(['creator', 'reference'])
            ->where('product_id', $product->id)
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('inventory.movements.kardex', compact('product', 'movements', 'initialBalance'));
    }

    public function kardexForm()
    {
        Gate::authorize('inventory.view');
        $products = Product::orderBy('name')->get();
        return view('inventory.movements.kardex_form', compact('products'));
    }
}
