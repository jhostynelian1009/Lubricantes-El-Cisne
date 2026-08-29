<?php

namespace App\Http\Controllers;

use App\Enums\StockEntryStatus;
use App\Http\Requests\Inventory\StoreStockEntryRequest;
use App\Http\Requests\Inventory\UpdateStockEntryRequest;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Models\Product;
use App\Services\StockEntryService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StockEntryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private StockEntryService $service) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', StockEntry::class);

        $query = StockEntry::with(['supplier', 'creator', 'confirmer'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $entries = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::where('active', true)->orderBy('name')->get();

        return view('inventory.entries.index', compact('entries', 'suppliers'));
    }

    public function create()
    {
        $this->authorize('create', StockEntry::class);
        $suppliers = Supplier::where('active', true)->orderBy('name')->get();
        $products = Product::where('active', true)->orderBy('name')->get();

        return view('inventory.entries.create', compact('suppliers', 'products'));
    }

    public function store(StoreStockEntryRequest $request)
    {
        $entry = $this->service->createDraft($request->validated(), $request->user());

        return redirect()->route('stock-entries.show', $entry)
            ->with('success', 'Borrador de entrada creado exitosamente.');
    }

    public function show(StockEntry $stockEntry)
    {
        $this->authorize('view', $stockEntry);
        $stockEntry->load(['supplier', 'creator', 'confirmer', 'details.product']);

        return view('inventory.entries.show', compact('stockEntry'));
    }

    public function edit(StockEntry $stockEntry)
    {
        $this->authorize('update', $stockEntry);
        $stockEntry->load(['details.product']);
        
        $suppliers = Supplier::where('active', true)->orderBy('name')->get();
        $products = Product::where('active', true)->orderBy('name')->get();

        return view('inventory.entries.edit', compact('stockEntry', 'suppliers', 'products'));
    }

    public function update(UpdateStockEntryRequest $request, StockEntry $stockEntry)
    {
        $this->service->updateDraft($stockEntry, $request->validated());

        return redirect()->route('stock-entries.show', $stockEntry)
            ->with('success', 'Borrador de entrada actualizado exitosamente.');
    }

    public function destroy(StockEntry $stockEntry)
    {
        $this->authorize('delete', $stockEntry);

        if ($stockEntry->status !== StockEntryStatus::DRAFT) {
            abort(409, 'Solo se pueden eliminar entradas en estado borrador.');
        }

        $stockEntry->details()->delete();
        $stockEntry->delete();

        return redirect()->route('stock-entries.index')
            ->with('success', 'Borrador eliminado exitosamente.');
    }

    public function confirm(StockEntry $stockEntry, Request $request)
    {
        $this->authorize('confirm', $stockEntry);

        $this->service->confirm($stockEntry, $request->user());

        return redirect()->route('stock-entries.show', $stockEntry)
            ->with('success', 'Entrada confirmada exitosamente. El inventario ha sido actualizado.');
    }
}
