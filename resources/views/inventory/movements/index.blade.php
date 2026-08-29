@extends('layouts.app')

@section('title', 'Historial de Movimientos')

@section('content')
<div class="container-fluid px-4">
    <h2 class="mt-4">Historial de Movimientos</h2>
    
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body bg-light">
            <form action="{{ route('inventory.movements.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Producto</label>
                    <select name="product_id" class="form-select form-select-sm">
                        <option value="">Todos los productos</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->sku }} - {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo de Movimiento</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Usuario</label>
                    <select name="created_by" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('created_by') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Fecha y Hora</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th class="text-end">Saldo Ant.</th>
                            <th class="text-end">Variación</th>
                            <th class="text-end">Saldo Post.</th>
                            <th>Usuario</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                            <tr>
                                <td>{{ $movement->id }}</td>
                                <td>{{ $movement->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <small class="text-muted d-block">{{ $movement->product->sku }}</small>
                                    {{ $movement->product->name }}
                                </td>
                                <td>
                                    @if($movement->type === 'entry')
                                        <span class="badge bg-success">Entrada</span>
                                    @elseif($movement->type === 'initial_adjustment')
                                        <span class="badge bg-primary">Carga Inicial</span>
                                    @elseif($movement->type === 'adjustment_in')
                                        <span class="badge bg-info">Ajuste (+)</span>
                                    @elseif($movement->type === 'adjustment_out')
                                        <span class="badge bg-warning text-dark">Ajuste (-)</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $movement->type }}</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ $movement->quantity_before }}</td>
                                <td class="text-end fw-bold {{ $movement->quantity_delta > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $movement->quantity_delta > 0 ? '+' : '' }}{{ $movement->quantity_delta }}
                                </td>
                                <td class="text-end fw-bold text-primary">{{ $movement->quantity_after }}</td>
                                <td>{{ $movement->creator->name }}</td>
                                <td>
                                    <small>{{ $movement->reason }}</small>
                                    @if($movement->reference_type === \App\Models\StockEntry::class)
                                        <br><a href="{{ route('stock-entries.show', $movement->reference_id) }}" class="badge bg-light text-dark text-decoration-none border">Ver Entrada</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">No se encontraron movimientos con los filtros aplicados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 mt-3">
            {{ $movements->links() }}
        </div>
    </div>
</div>
@endsection
