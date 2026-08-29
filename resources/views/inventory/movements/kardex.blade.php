@extends('layouts.app')

@section('title', 'Reporte Kardex')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2>Reporte Kardex</h2>
        <a href="{{ route('inventory.kardex.form') }}" class="btn btn-outline-secondary">Volver al Filtro</a>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-1 fw-bold text-primary">{{ $product->sku }} - {{ $product->name }}</h5>
                    <p class="mb-0 text-muted">Unidad: {{ $product->unit }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1"><strong>Período:</strong> {{ request('start_date') }} al {{ request('end_date') }}</p>
                    <p class="mb-0"><strong>Generado por:</strong> {{ Auth::user()->name }} ({{ now()->format('Y-m-d H:i') }})</p>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0">
                    <thead class="table-dark text-center align-middle">
                        <tr>
                            <th rowspan="2" style="width: 15%">Fecha</th>
                            <th rowspan="2" style="width: 20%">Concepto / Origen</th>
                            <th colspan="3" class="bg-success text-white border-bottom-0">Entradas</th>
                            <th colspan="3" class="bg-danger text-white border-bottom-0">Salidas</th>
                            <th colspan="3" class="bg-primary text-white border-bottom-0">Saldos</th>
                        </tr>
                        <tr>
                            <th class="bg-success text-white">Cant.</th>
                            <th class="bg-success text-white">C.U.</th>
                            <th class="bg-success text-white">Total</th>
                            
                            <th class="bg-danger text-white">Cant.</th>
                            <th class="bg-danger text-white">C.U.</th>
                            <th class="bg-danger text-white">Total</th>
                            
                            <th class="bg-primary text-white">Cant.</th>
                            <th class="bg-primary text-white">C.U.</th>
                            <th class="bg-primary text-white">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Saldo Inicial -->
                        <tr class="table-warning">
                            <td>{{ request('start_date') }}</td>
                            <td class="fw-bold">SALDO INICIAL</td>
                            <td colspan="6"></td>
                            <td class="text-end fw-bold">{{ $initialBalance }}</td>
                            <td class="text-end text-muted">-</td>
                            <td class="text-end text-muted">-</td>
                        </tr>

                        @php
                            $currentBalance = (float)$initialBalance;
                        @endphp

                        @forelse($movements as $mov)
                            @php
                                $isEntry = $mov->quantity_delta > 0;
                                $qty = abs((float)$mov->quantity_delta);
                                $cost = (float)$mov->unit_cost;
                                $total = $qty * $cost;
                                $currentBalance = (float)$mov->quantity_after;
                            @endphp
                            <tr>
                                <td>{{ $mov->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <small class="d-block text-muted">{{ $mov->creator->name }}</small>
                                    {{ $mov->reason }}
                                </td>
                                
                                <!-- Entradas -->
                                <td class="text-end">{{ $isEntry ? number_format($qty, 3) : '' }}</td>
                                <td class="text-end">{{ $isEntry && $cost > 0 ? number_format($cost, 2) : '' }}</td>
                                <td class="text-end">{{ $isEntry && $total > 0 ? number_format($total, 2) : '' }}</td>
                                
                                <!-- Salidas -->
                                <td class="text-end">{{ !$isEntry ? number_format($qty, 3) : '' }}</td>
                                <td class="text-end">{{ !$isEntry && $cost > 0 ? number_format($cost, 2) : '' }}</td>
                                <td class="text-end">{{ !$isEntry && $total > 0 ? number_format($total, 2) : '' }}</td>
                                
                                <!-- Saldos -->
                                <td class="text-end fw-bold">{{ number_format($currentBalance, 3) }}</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">No se registraron movimientos en este período.</td>
                            </tr>
                        @endforelse
                        
                        <!-- Saldo Final -->
                        <tr class="table-primary fw-bold">
                            <td colspan="8" class="text-end">SALDO FINAL DEL PERÍODO</td>
                            <td class="text-end fs-5">{{ number_format($currentBalance, 3) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
