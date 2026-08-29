@extends('layouts.app')

@section('title', "Producto «{$product->name}» — Catálogos e Inventario")

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">{{ $product->name }}</h1>
        <p class="text-muted mb-0 font-monospace">SKU: {{ $product->sku }} | Barcode: {{ $product->barcode ?? 'N/A' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            ← Volver al listado
        </a>
        @can('update', $product)
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                ✏️ Editar Producto
            </a>
        @endcan
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold">Información General del Producto</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-muted small d-block">CATEGORÍA</span>
                        <span class="fw-bold">{{ $product->category->name ?? 'Sin Categoría' }}</span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">UNIDAD DE MEDIDA</span>
                        <span class="fw-bold">{{ config("inventory.units.{$product->unit}", $product->unit) }} ({{ $product->unit }})</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted small d-block">PRECIO DE VENTA</span>
                        <span class="fs-5 font-monospace fw-bold text-success">${{ number_format($product->sale_price, 2) }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted small d-block">COSTO REFERENCIAL (ÚLTIMO)</span>
                        <span class="fs-5 font-monospace">${{ number_format($product->last_cost, 2) }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted small d-block">STOCK MÍNIMO</span>
                        <span class="fs-5 font-monospace">{{ number_format($product->minimum_stock, 3) }}</span>
                    </div>
                    <div class="col-md-12">
                        <span class="text-muted small d-block">DESCRIPCIÓN / DETALLES</span>
                        <p class="mb-0 text-secondary">{{ $product->description ?? 'Sin descripción adicional.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold">Estado del Inventario</h5>
            </div>
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="mb-3">
                    <span class="display-6 font-monospace fw-bold">{{ number_format($product->current_stock, 3) }}</span>
                    <span class="text-muted d-block small">{{ config("inventory.units.{$product->unit}", $product->unit) }} en stock</span>
                </div>
                <div class="mb-3">
                    <span class="badge {{ $product->stock_status_badge_class }} fs-6 px-3 py-2">
                        {{ $product->stock_status_label }}
                    </span>
                </div>
                <div>
                    @if($product->active)
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Producto Activo</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Producto Inactivo</span>
                    @endif
                </div>

                @can('initialStock', $product)
                    @if($product->active && (float)$product->current_stock == 0 && !$product->hasMovements())
                        <div class="mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-warning w-100 font-semibold" data-bs-toggle="modal" data-bs-target="#initialStockModal">
                                📦 Realizar Carga Inicial
                            </button>
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</div>

{{-- Historial de Movimientos --}}
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold">Historial de Movimientos de Inventario</h5>
        <small class="text-muted">Kardex cronológico de entradas y salidas</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Tipo de Movimiento</th>
                        <th class="text-end">Delta (Cantidad)</th>
                        <th class="text-end">Stock Antes</th>
                        <th class="text-end">Stock Después</th>
                        <th class="text-end">Costo Unit.</th>
                        <th>Motivo / Observación</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td class="font-monospace small">
                                {{ $movement->created_at ? $movement->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                            </td>
                            <td>
                                @if($movement->type === 'initial_adjustment')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Carga Inicial</span>
                                @elseif($movement->type === 'entry')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Entrada</span>
                                @elseif($movement->type === 'sale')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Venta</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border">{{ $movement->type }}</span>
                                @endif
                            </td>
                            <td class="text-end font-monospace fw-bold {{ (float)$movement->quantity_delta > 0 ? 'text-success' : 'text-danger' }}">
                                {{ (float)$movement->quantity_delta > 0 ? '+' : '' }}{{ number_format($movement->quantity_delta, 3) }}
                            </td>
                            <td class="text-end font-monospace text-muted">
                                {{ number_format($movement->quantity_before, 3) }}
                            </td>
                            <td class="text-end font-monospace fw-bold">
                                {{ number_format($movement->quantity_after, 3) }}
                            </td>
                            <td class="text-end font-monospace">
                                {{ $movement->unit_cost !== null ? '$' . number_format($movement->unit_cost, 2) : '—' }}
                            </td>
                            <td class="small text-muted">
                                {{ $movement->reason ?? '—' }}
                            </td>
                            <td class="small">
                                {{ $movement->creator->name ?? 'Sistema' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No existen movimientos registrados para este producto.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($movements->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $movements->links() }}
        </div>
    @endif
</div>

{{-- Modal Carga Inicial --}}
@can('initialStock', $product)
    @if($product->active && (float)$product->current_stock == 0 && !$product->hasMovements())
        <div class="modal fade" id="initialStockModal" tabindex="-1" aria-labelledby="initialStockModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('products.initial-stock', $product) }}" onsubmit="return confirm('¿Está seguro de confirmar la carga inicial de inventario? Esta acción no se puede deshacer.');">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="initialStockModalLabel">📦 Carga Inicial de Inventario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning mb-3">
                                <strong>Atención:</strong> La carga inicial solo puede ejecutarse una única vez por producto cuando el stock inicial es cero.
                            </div>
                            <div class="mb-3">
                                <label for="modal_quantity" class="form-label fw-bold">Cantidad Inicial <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" min="0.001" name="quantity" id="modal_quantity" class="form-control" required placeholder="Ej: 50.000">
                                <small class="text-muted">Unidad: {{ config("inventory.units.{$product->unit}", $product->unit) }} (Máx. 3 decimales)</small>
                            </div>
                            <div class="mb-3">
                                <label for="modal_unit_cost" class="form-label fw-bold">Costo Unitario ($)</label>
                                <input type="number" step="0.01" min="0" name="unit_cost" id="modal_unit_cost" class="form-control" value="{{ $product->last_cost }}" placeholder="0.00">
                                <small class="text-muted">Dejar por defecto tomará el costo referencial (${{ number_format($product->last_cost, 2) }}).</small>
                            </div>
                            <div class="mb-3">
                                <label for="modal_reason" class="form-label fw-bold">Motivo / Observación</label>
                                <input type="text" name="reason" id="modal_reason" class="form-control" value="Carga inicial de inventario" maxlength="500">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Registrar Carga Inicial</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endcan
@endsection
