@extends('layouts.app')

@section('title', 'Editar Producto — Catálogos e Inventario')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0 text-gray-800">Editar Producto «{{ $product->name }}»</h1>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
                ← Volver al listado
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('products.update', $product) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="sku" class="form-label fw-bold">SKU (Código único) <span class="text-danger">*</span></label>
                            @if($product->hasMovements())
                                <input type="text" class="form-control bg-light @error('sku') is-invalid @enderror" value="{{ $product->sku }}" readonly title="Bloqueado por movimientos registrados">
                                <input type="hidden" name="sku" value="{{ $product->sku }}">
                                <small class="form-text text-warning font-monospace">🔒 SKU bloqueado: el producto ya registra movimientos.</small>
                            @else
                                <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" required>
                            @endif
                            @error('sku')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="barcode" class="form-label fw-bold">Código de Barras</label>
                            <input type="text" name="barcode" id="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode', $product->barcode) }}">
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="name" class="form-label fw-bold">Nombre del Producto <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-bold">Categoría <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Seleccione una categoría</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }} {{ !$category->active ? '(Inactiva)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="unit" class="form-label fw-bold">Unidad de Medida <span class="text-danger">*</span></label>
                            <select name="unit" id="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                <option value="">Seleccione una unidad</option>
                                @foreach($units as $key => $label)
                                    <option value="{{ $key }}" {{ old('unit', $product->unit) === $key ? 'selected' : '' }}>
                                        {{ $label }} ({{ $key }})
                                    </option>
                                @endforeach
                            </select>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="minimum_stock" class="form-label fw-bold">Stock Mínimo <span class="text-danger">*</span></label>
                            <input type="number" step="0.001" min="0" name="minimum_stock" id="minimum_stock" class="form-control @error('minimum_stock') is-invalid @enderror" value="{{ old('minimum_stock', $product->minimum_stock) }}" required>
                            @error('minimum_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="last_cost" class="form-label fw-bold">Costo Referencial ($)</label>
                            <input type="number" step="0.01" min="0" name="last_cost" id="last_cost" class="form-control @error('last_cost') is-invalid @enderror" value="{{ old('last_cost', $product->last_cost) }}">
                            @error('last_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="sale_price" class="form-label fw-bold">Precio de Venta ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="sale_price" id="sale_price" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price', $product->sale_price) }}" required>
                            @error('sale_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold text-muted">Stock Actual (Solo Lectura)</label>
                            <input type="text" class="form-control bg-light font-monospace fw-bold" value="{{ number_format($product->current_stock, 3) }}" disabled readonly>
                            <small class="form-text text-muted">El stock actual no se modifica directamente desde el formulario. Use los procesos de movimientos autorizados.</small>
                        </div>

                        <div class="col-md-12">
                            <label for="description" class="form-label fw-bold">Descripción / Detalles</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar Producto</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
