@extends('layouts.app')

@section('title', 'Productos — Catálogos e Inventario')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">Catálogo de Productos</h1>
        <p class="text-muted mb-0">Gestión de productos y control del núcleo de inventario.</p>
    </div>
    @can('products.manage')
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <span class="me-1">➕</span> Nuevo Producto
        </a>
    @endcan
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('products.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label text-muted small fw-bold">BÚSQUEDA</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Nombre, SKU o código de barras..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="category_id" class="form-label text-muted small fw-bold">CATEGORÍA</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label text-muted small fw-bold">ESTADO PRODUCTO</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="stock_status" class="form-label text-muted small fw-bold">ESTADO STOCK</label>
                <select name="stock_status" id="stock_status" class="form-select">
                    <option value="">Todos los stocks</option>
                    <option value="normal" {{ request('stock_status') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Bajo stock</option>
                    <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Agotado</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                @if(request()->hasAny(['search', 'category_id', 'status', 'stock_status']))
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">✖</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SKU / Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-end">Stock Actual</th>
                        <th class="text-end">Precio Venta</th>
                        <th>Estado Stock</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <span class="fw-bold font-monospace">{{ $product->sku }}</span>
                                @if($product->barcode)
                                    <br><small class="text-muted font-monospace">📷 {{ $product->barcode }}</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('products.show', $product) }}" class="fw-bold text-decoration-none text-dark">
                                    {{ $product->name }}
                                </a>
                                <br><small class="text-muted">{{ config("inventory.units.{$product->unit}", $product->unit) }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $product->category->name ?? 'N/A' }}</span>
                            </td>
                            <td class="text-end font-monospace fw-bold">
                                {{ number_format($product->current_stock, 3) }}
                            </td>
                            <td class="text-end font-monospace">
                                ${{ number_format($product->sale_price, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $product->stock_status_badge_class }}">
                                    {{ $product->stock_status_label }}
                                </span>
                            </td>
                            <td>
                                @if($product->active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Activo</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-info" title="Ver detalle">
                                        Ver
                                    </a>
                                    @can('update', $product)
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary" title="Editar">
                                            Editar
                                        </a>
                                    @endcan
                                    @can('toggleStatus', $product)
                                        <form method="POST" action="{{ route('products.toggle-status', $product) }}" class="d-inline" onsubmit="return confirm('¿Está seguro de cambiar el estado de este producto?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning">
                                                {{ $product->active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No se encontraron productos registrados con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
