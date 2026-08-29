@extends('layouts.app')

@section('title', 'Entradas de Stock')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2>Entradas de Stock</h2>
        @can('create', \App\Models\StockEntry::class)
            <a href="{{ route('stock-entries.create') }}" class="btn btn-primary">
                Nueva Entrada
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body bg-light">
            <form action="{{ route('stock-entries.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="supplier_id" class="form-select form-select-sm">
                        <option value="">Todos los proveedores</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
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
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Referencia</th>
                            <th>Estado</th>
                            <th>Líneas</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td class="fw-bold">{{ $entry->number ?? 'BORRADOR-' . $entry->id }}</td>
                                <td>{{ $entry->entry_date->format('Y-m-d') }}</td>
                                <td>{{ $entry->supplier ? $entry->supplier->name : 'N/A' }}</td>
                                <td>{{ $entry->reference ?? '-' }}</td>
                                <td>
                                    @if($entry->status === \App\Enums\StockEntryStatus::DRAFT)
                                        <span class="badge bg-warning text-dark">Borrador</span>
                                    @else
                                        <span class="badge bg-success">Confirmada</span>
                                    @endif
                                </td>
                                <td>{{ $entry->details()->count() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('stock-entries.show', $entry) }}" class="btn btn-sm btn-info text-white">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No se encontraron entradas de stock.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 mt-3">
            {{ $entries->links() }}
        </div>
    </div>
</div>
@endsection
