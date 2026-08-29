<?php

namespace App\Http\Controllers;

use App\Enums\SaleStatus;
use App\Http\Requests\Sales\ConfirmSaleRequest;
use App\Http\Requests\Sales\StoreSaleDraftRequest;
use App\Http\Requests\Sales\UpdateSaleDraftRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Sale::class);

        $query = Sale::with(['customer', 'creator', 'confirmer'])->latest('id');

        if ($request->filled('number')) {
            $query->where('number', 'LIKE', '%' . trim($request->number) . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
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

        $sales = $query->paginate(15)->withQueryString();
        $customers = Customer::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $statuses = SaleStatus::cases();

        return view('sales.index', compact('sales', 'customers', 'users', 'statuses'));
    }

    public function create(): View
    {
        Gate::authorize('create', Sale::class);

        $customers = Customer::active()->orderBy('name')->get();
        return view('sales.create', compact('customers'));
    }

    public function store(StoreSaleDraftRequest $request): RedirectResponse|JsonResponse
    {
        $draft = \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $customer = $request->customer_id ? Customer::find($request->customer_id) : null;
            $draft = $this->saleService->createDraft($customer, $request->user());

            if ($request->filled('details')) {
                $draft = $this->saleService->replaceLines($draft, $request->details, $request->user(), $customer);
            }

            return $draft;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'sale' => $draft,
                'redirect' => route('sales.edit', $draft),
            ]);
        }

        return redirect()->route('sales.edit', $draft)->with('success', 'Borrador de venta creado exitosamente.');
    }

    public function show(Sale $sale): View
    {
        Gate::authorize('view', $sale);

        $sale->load(['customer', 'creator', 'confirmer', 'details.product']);
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale): View|RedirectResponse
    {
        Gate::authorize('update', $sale);

        if (!$sale->isDraft()) {
            throw new ConflictHttpException('Solo se pueden editar ventas en estado borrador.');
        }

        $sale->load(['customer', 'details']);
        $customers = Customer::active()->orderBy('name')->get();

        return view('sales.edit', compact('sale', 'customers'));
    }

    public function update(UpdateSaleDraftRequest $request, Sale $sale): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $sale);

        if (!$sale->isDraft()) {
            throw new ConflictHttpException('Solo se pueden actualizar ventas en estado borrador.');
        }

        $customer = $request->filled('customer_id') ? Customer::find($request->customer_id) : null;
        $draft = $this->saleService->replaceLines($sale, $request->input('details', []), $request->user(), $customer);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'sale' => $draft,
            ]);
        }

        return redirect()->route('sales.edit', $draft)->with('success', 'Borrador actualizado correctamente.');
    }

    public function destroy(Sale $sale): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $sale);

        if (!$sale->isDraft()) {
            throw new ConflictHttpException('Solo se pueden eliminar ventas en estado borrador.');
        }

        $sale->details()->delete();
        $sale->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'redirect' => route('sales.index')]);
        }

        return redirect()->route('sales.index')->with('success', 'Borrador de venta descartado.');
    }

    public function confirm(ConfirmSaleRequest $request, Sale $sale): RedirectResponse|JsonResponse
    {
        $confirmedSale = $this->saleService->confirm($sale, $request->user());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'sale' => $confirmedSale,
                'redirect' => route('sales.receipt', $confirmedSale),
            ]);
        }

        return redirect()->route('sales.receipt', $confirmedSale)->with('success', 'Venta confirmada exitosamente.');
    }

    public function receipt(Sale $sale): View
    {
        Gate::authorize('receipt', $sale);

        if (!$sale->isConfirmed()) {
            abort(403, 'El comprobante solo está disponible para ventas confirmadas.');
        }

        $sale->load(['customer', 'creator', 'confirmer', 'details']);
        return view('sales.receipt', compact('sale'));
    }

    public function searchProducts(Request $request): JsonResponse
    {
        Gate::authorize('create', Sale::class);

        $term = trim((string) $request->input('q', ''));

        if (mb_strlen($term) === 0) {
            return response()->json([]);
        }

        $products = Product::query()
            ->where('active', true)
            ->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', '%' . $term . '%')
                    ->orWhere('sku', 'LIKE', '%' . $term . '%')
                    ->orWhere('barcode', 'LIKE', '%' . $term . '%');
            })
            ->orderBy('name', 'asc')
            ->limit(20)
            ->get(['id', 'name', 'sku', 'barcode', 'unit', 'current_stock', 'sale_price']);

        return response()->json($products);
    }
}
