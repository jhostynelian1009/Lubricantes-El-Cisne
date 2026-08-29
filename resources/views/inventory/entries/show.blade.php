@extends('layouts.app')

@section('title', 'Detalle de Entrada de Stock')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2>Detalle de Entrada #{{ $stockEntry->number ?? $stockEntry->id }}</h2>
        <div>
            <a href="{{ route('stock-entries.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
            @if($stockEntry->status === \App\Enums\StockEntryStatus::DRAFT)
                @can('update', $stockEntry)
                    <a href="{{ route('stock-entries.edit', $stockEntry) }}" class="btn btn-primary">Editar Borrador</a>
                @endcan
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted" style="width: 40%">Estado</th>
                                <td>
                                    @if($stockEntry->status === \App\Enums\StockEntryStatus::DRAFT)
                                        <span class="badge bg-warning text-dark fs-6">Borrador</span>
                                    @else
                                        <span class="badge bg-success fs-6">Confirmada</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Fecha</th>
                                <td>{{ $stockEntry->entry_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Proveedor</th>
                                <td>{{ $stockEntry->supplier ? $stockEntry->supplier->name : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Referencia</th>
                                <td>{{ $stockEntry->reference ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Creado por</th>
                                <td>{{ $stockEntry->creator->name }}<br><small class="text-muted">{{ $stockEntry->created_at->format('d/m/Y H:i') }}</small></td>
                            </tr>
                            @if($stockEntry->status === \App\Enums\StockEntryStatus::CONFIRMED)
                                <tr>
                                    <th class="text-muted">Confirmado por</th>
                                    <td>{{ $stockEntry->confirmer->name ?? 'N/A' }}<br><small class="text-muted">{{ $stockEntry->confirmed_at ? $stockEntry->confirmed_at->format('d/m/Y H:i') : '' }}</small></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    
                    @if($stockEntry->notes)
                        <hr>
                        <h6 class="text-muted mb-1">Notas:</h6>
                        <p class="mb-0">{{ $stockEntry->notes }}</p>
                    @endif
                </div>
                @if($stockEntry->status === \App\Enums\StockEntryStatus::DRAFT)
                    <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                        <div class="d-grid gap-2">
                            @can('confirm', $stockEntry)
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal">
                                    Confirmar Entrada
                                </button>
                            @endcan
                            @can('delete', $stockEntry)
                                <form action="{{ route('stock-entries.destroy', $stockEntry) }}" method="POST" class="d-grid" onsubmit="return confirm('¿Está seguro de eliminar este borrador?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">Eliminar Borrador</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Detalle de Productos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>SKU</th>
                                    <th>Producto</th>
                                    <th>Unidad</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Costo U.</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $grandTotal = 0; @endphp
                                @foreach($stockEntry->details as $detail)
                                    @php $grandTotal += $detail->line_total; @endphp
                                    <tr>
                                        <td>{{ $detail->product_sku }}</td>
                                        <td>{{ $detail->product_name }}</td>
                                        <td>{{ $detail->unit }}</td>
                                        <td class="text-end">{{ number_format($detail->quantity, 3) }}</td>
                                        <td class="text-end">{{ number_format($detail->unit_cost, 2) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($detail->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end fs-5">Total General:</th>
                                    <th class="text-end fs-5 text-primary">${{ number_format($grandTotal, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($stockEntry->status === \App\Enums\StockEntryStatus::DRAFT)
<!-- Modal Confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="confirmModalLabel">Confirmar Entrada de Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <h6 class="alert-heading fw-bold">⚠️ ¡Atención! Acción irreversible</h6>
                    <p class="mb-0">Al confirmar esta entrada:</p>
                    <ul class="mb-0 mt-2">
                        <li>El stock de los <strong>{{ $stockEntry->details->count() }} productos</strong> aumentará inmediatamente.</li>
                        <li>Se registrará el movimiento en el historial (Kardex).</li>
                        <li>Se actualizará el costo unitario de los productos.</li>
                        <li><strong>No podrá editar ni eliminar</strong> esta entrada posteriormente.</li>
                    </ul>
                </div>
                <p>¿Está completamente seguro de proceder?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('stock-entries.confirm', $stockEntry) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerHTML='Confirmando...'; this.form.submit();">
                        Sí, Confirmar Entrada
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
