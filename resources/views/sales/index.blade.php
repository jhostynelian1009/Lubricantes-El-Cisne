@extends('layouts.app')

@section('title', 'Historial de Ventas — Lubricantes «El Cisne»')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark">Historial de Ventas</h1>
            <p class="text-muted small mb-0">Consulte y filtre las ventas registradas y confirmadas</p>
        </div>
        @can('sales.create')
            <a href="{{ route('sales.create') }}" class="btn btn-primary shadow-sm fw-semibold">
                ➕ Nueva Venta (POS)
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sales.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label for="number" class="form-label small fw-semibold">Número de Venta</label>
                    <input type="text" name="number" id="number" class="form-control form-control-sm" value="{{ request('number') }}" placeholder="Ej: V-2026-000001">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label small fw-semibold">Estado</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">-- Todos --</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="customer_id" class="form-label small fw-semibold">Cliente</label>
                    <select name="customer_id" id="customer_id" class="form-select form-select-sm">
                        <option value="">-- Todos --</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ (string) request('customer_id') === (string) $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="created_by" class="form-label small fw-semibold">Usuario</label>
                    <select name="created_by" id="created_by" class="form-select form-select-sm">
                        <option value="">-- Todos --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ (string) request('created_by') === (string) $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="start_date" class="form-label small fw-semibold">Desde</label>
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label for="end_date" class="form-label small fw-semibold">Hasta</label>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de resultados --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Registrado por</th>
                        <th>Estado</th>
                        <th class="text-end">Total (USD)</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="fw-bold">
                                @if ($sale->number)
                                    <span class="text-primary">{{ $sale->number }}</span>
                                @else
                                    <span class="text-muted fst-italic">Sin número (Borrador)</span>
                                @endif
                            </td>
                            <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($sale->customer)
                                    <span class="fw-semibold">{{ $sale->customer->name }}</span>
                                @else
                                    <span class="text-muted">Consumidor final</span>
                                @endif
                            </td>
                            <td>{{ $sale->creator?->name ?? 'Sistema' }}</td>
                            <td>
                                @if ($sale->isConfirmed())
                                    <span class="badge bg-success">Confirmada</span>
                                @elseif ($sale->isDraft())
                                    <span class="badge bg-warning text-dark">Borrador</span>
                                @else
                                    <span class="badge bg-secondary">Anulada</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold fs-6">${{ number_format((float)$sale->total, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-info btn-sm" title="Ver Detalle">👁️ Ver</a>
                                @if ($sale->isDraft())
                                    @can('update', $sale)
                                        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-outline-warning btn-sm" title="Editar Borrador">✏️ Editar</a>
                                    @endcan
                                @elseif ($sale->isConfirmed())
                                    <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-outline-secondary btn-sm" title="Comprobante Imprimible">📄 Comprobante</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No se encontraron ventas registradas que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sales->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
