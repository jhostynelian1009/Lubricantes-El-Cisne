<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\InitialStockRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('category_id')) {
            $query->category($request->input('category_id'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->active();
            } elseif ($request->input('status') === 'inactive') {
                $query->where('active', false);
            }
        }

        if ($request->filled('stock_status')) {
            $query->stockStatus($request->input('stock_status'));
        }

        $products = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();
        $categories = Category::active()->orderBy('name', 'asc')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        $categories = Category::active()->orderBy('name', 'asc')->get();
        $units = config('inventory.units', []);

        return view('products.create', compact('categories', 'units'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['active'] = true;

        $product = Product::create($data);

        return redirect()->route('products.index')
            ->with('success', "Producto «{$product->name}» creado correctamente.");
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load(['category']);
        $movements = $product->inventoryMovements()
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('products.show', compact('product', 'movements'));
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::active()->orderBy('name', 'asc')->get();
        if ($product->category && !$product->category->active) {
            $categories->push($product->category);
        }

        $units = config('inventory.units', []);

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', "Producto «{$product->name}» actualizado correctamente.");
    }

    public function toggleStatus(Product $product)
    {
        $this->authorize('toggleStatus', $product);

        $product->update(['active' => !$product->active]);

        $statusName = $product->active ? 'activado' : 'desactivado';

        return redirect()->back()
            ->with('success', "Producto «{$product->name}» {$statusName} correctamente.");
    }

    public function initialStock(InitialStockRequest $request, Product $product, StockService $stockService)
    {
        $validated = $request->validated();

        $unitCost = $validated['unit_cost'] ?? $product->last_cost;

        $stockService->applyMovement(
            product: $product,
            type: 'initial_adjustment',
            quantityDelta: $validated['quantity'],
            user: $request->user(),
            reason: $validated['reason'],
            unitCost: $unitCost,
            reference: $product
        );

        return redirect()->route('products.show', $product)
            ->with('success', "Carga inicial de inventario registrada correctamente para «{$product->name}».");
    }
}
