@extends('layouts.app')

@section('title', 'Comprobante Interno ' . $sale->number . ' — Lubricantes «El Cisne»')

@section('content')
<div class="container py-4">
    {{-- Controles en pantalla (se ocultan al imprimir) --}}
    <div class="d-print-none mb-4 d-flex justify-content-between align-items-center">
        <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-secondary">
            ⬅️ Volver a la Venta
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.index') }}" class="btn btn-outline-primary">📋 Historial</a>
            <button type="button" onclick="window.print()" class="btn btn-success fw-bold">
                🖨️ Imprimir Comprobante
            </button>
        </div>
    </div>

    {{-- Vista del Comprobante (Monocromático e Imprimible) --}}
    <div class="card border border-dark p-4 mx-auto shadow-sm receipt-box" style="max-width: 800px; background-color: #fff;">
        {{-- Encabezado --}}
        <div class="text-center pb-3 mb-3 border-bottom border-dark">
            <h2 class="fw-bold mb-1">Lubricantes «El Cisne»</h2>
            <h5 class="text-uppercase fw-semibold mb-2">Comprobante Interno de Venta</h5>
            <div class="badge bg-dark text-white fs-6 px-3 py-2">
                N°: {{ $sale->number }}
            </div>
        </div>

        {{-- Aviso Obligatorio Requerido por la Especificación --}}
        <div class="alert alert-dark text-center fw-bold py-2 mb-4 border border-dark small rounded-0">
            Comprobante interno — no constituye factura electrónica autorizada
        </div>

        {{-- Datos de la Venta --}}
        <div class="row mb-4">
            <div class="col-6">
                <p class="mb-1"><strong>Fecha y Hora:</strong> {{ $sale->confirmed_at?->format('d/m/Y H:i') ?? $sale->created_at->format('d/m/Y H:i') }}</p>
                <p class="mb-1">
                    <strong>Cliente:</strong> {{ $sale->customer?->name ?? 'Consumidor final' }}
                </p>
                @if ($sale->customer?->identification)
                    <p class="mb-1"><strong>Identificación:</strong> {{ $sale->customer->identification }}</p>
                @endif
            </div>
            <div class="col-6 text-end">
                <p class="mb-1"><strong>Atendido por:</strong> {{ $sale->confirmer?->name ?? $sale->creator?->name ?? 'Sistema' }}</p>
                <p class="mb-1"><strong>Moneda:</strong> USD ($)</p>
            </div>
        </div>

        {{-- Tabla de Detalles --}}
        <table class="table table-bordered border-dark align-middle mb-4">
            <thead class="table-light border-dark">
                <tr>
                    <th class="text-center" style="width: 90px;">Cant.</th>
                    <th class="text-center" style="width: 80px;">Unidad</th>
                    <th>Producto</th>
                    <th class="text-end" style="width: 110px;">P. Unit.</th>
                    <th class="text-end" style="width: 120px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->details as $detail)
                    <tr>
                        <td class="text-center fw-semibold">{{ number_format((float)$detail->quantity, 3) }}</td>
                        <td class="text-center">{{ $detail->unit }}</td>
                        <td>
                            <strong class="d-block text-dark">{{ $detail->product_name }}</strong>
                            <small class="text-muted">SKU: {{ $detail->product_sku }}</small>
                        </td>
                        <td class="text-end">${{ number_format((float)$detail->unit_price, 2) }}</td>
                        <td class="text-end fw-bold">${{ number_format((float)$detail->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                    <td class="text-end fw-bold">${{ number_format((float)$sale->subtotal, 2) }}</td>
                </tr>
                <tr class="border-top border-dark">
                    <td colspan="4" class="text-end fw-bold fs-5">TOTAL USD:</td>
                    <td class="text-end fw-bold fs-5">${{ number_format((float)$sale->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Pie de página del comprobante --}}
        <div class="text-center pt-3 border-top border-dark mt-4">
            <p class="small text-muted mb-0">¡Gracias por su preferencia! — Lubricantes «El Cisne»</p>
        </div>
    </div>
</div>

<style>
@media print {
    /* Ocultar encabezados, navegadores y controles */
    nav, footer, .d-print-none, .navbar, .navbar-cisne {
        display: none !important;
    }

    body {
        background-color: #fff !important;
        color: #000 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .container {
        width: 100% !important;
        max-width: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .receipt-box {
        border: 2px solid #000 !important;
        box-shadow: none !important;
        padding: 20px !important;
        max-width: 100% !important;
    }

    .alert-dark {
        background-color: #eee !important;
        color: #000 !important;
        border: 1px solid #000 !important;
    }

    .table-bordered, .table-bordered th, .table-bordered td {
        border: 1px solid #000 !important;
    }
}
</style>
@endsection
