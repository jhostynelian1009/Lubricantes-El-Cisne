@extends('layouts.app')

@section('title', 'Detalle de Venta — Lubricantes «El Cisne»')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark">
                Venta {{ $sale->number ?? 'Borrador #' . $sale->id }}
            </h1>
            <p class="text-muted small mb-0">Detalle general del registro de venta</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">📋 Volver al Historial</a>
            @if ($sale->isDraft())
                @can('update', $sale)
                    <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning btn-sm fw-semibold">✏️ Editar Borrador</a>
                @endcan
            @elseif ($sale->isConfirmed())
                <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-primary btn-sm fw-semibold">📄 Ver Comprobante Imprimible</a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold">Información General</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small d-block">Estado</label>
                        @if ($sale->isConfirmed())
                            <span class="badge bg-success fs-6">Confirmada</span>
                        @elseif ($sale->isDraft())
                            <span class="badge bg-warning text-dark fs-6">Borrador</span>
                        @else
                            <span class="badge bg-secondary fs-6">Anulada</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Cliente</label>
                        <span class="fw-bold">{{ $sale->customer?->name ?? 'Consumidor final' }}</span>
                        @if ($sale->customer?->identification)
                            <small class="text-muted d-block">ID: {{ $sale->customer->identification }}</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Registrado por</label>
                        <span class="fw-semibold">{{ $sale->creator?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Fecha de creación</label>
                        <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if ($sale->confirmed_at)
                        <div class="mb-3">
                            <label class="text-muted small d-block">Fecha de confirmación</label>
                            <span>{{ $sale->confirmed_at->format('d/m/Y H:i') }}</span>
                            @if ($sale->confirmer)
                                <small class="text-muted d-block">Por: {{ $sale->confirmer->name }}</small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold">Detalle de Productos</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>SKU</th>
                                <th>Producto</th>
                                <th>Unidad</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sale->details as $detail)
                                <tr>
                                    <td><code>{{ $detail->product_sku }}</code></td>
                                    <td class="fw-semibold">{{ $detail->product_name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $detail->unit }}</span></td>
                                    <td class="text-end fw-semibold">{{ number_format((float)$detail->quantity, 3) }}</td>
                                    <td class="text-end">${{ number_format((float)$detail->unit_price, 2) }}</td>
                                    <td class="text-end fw-bold text-primary">${{ number_format((float)$detail->line_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Esta venta no posee productos agregados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light border-top">
                            <tr>
                                <td colspan="5" class="text-end fw-bold fs-5">Subtotal:</td>
                                <td class="text-end fw-bold fs-5">${{ number_format((float)$sale->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-bold fs-4 text-success">TOTAL (USD):</td>
                                <td class="text-end fw-bold fs-4 text-success">${{ number_format((float)$sale->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
