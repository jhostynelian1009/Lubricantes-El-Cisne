@extends('layouts.app')

@section('title', 'Nuevo Producto — Catálogos e Inventario')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0 text-gray-800">Crear Nuevo Producto</h1>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
                ← Volver al listado
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('products.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="sku" class="form-label fw-bold">SKU (Código único) <span class="text-danger">*</span></label>
                            <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="Ej: ACE-SINT-5W30" required>
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="barcode" class="form-label fw-bold">Código de Barras</label>
                            <input type="text" name="barcode" id="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode') }}" placeholder="Ej: 7861001234567">
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="name" class="form-label fw-bold">Nombre del Producto <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ej: Aceite Sintético 5W-30 Shell Helix" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-bold">Categoría <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Seleccione una categoría</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
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
                                    <option value="{{ $key }}" {{ old('unit', 'galon') === $key ? 'selected' : '' }}>
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
                            <input type="number" step="0.001" min="0" name="minimum_stock" id="minimum_stock" class="form-control @error('minimum_stock') is-invalid @enderror" value="{{ old('minimum_stock', '0.000') }}" required>
                            @error('minimum_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="last_cost" class="form-label fw-bold">Costo Referencial ($)</label>
                            <input type="number" step="0.01" min="0" name="last_cost" id="last_cost" class="form-control @error('last_cost') is-invalid @enderror" value="{{ old('last_cost', '0.00') }}">
                            @error('last_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="sale_price" class="form-label fw-bold">Precio de Venta ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="sale_price" id="sale_price" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price') }}" required placeholder="0.00">
                            @error('sale_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold text-muted">Stock Inicial Actual</label>
                            <input type="text" class="form-control bg-light" value="0.000" disabled readonly>
                            <small class="form-text text-muted">Todo nuevo producto inicia obligatoriamente en 0.000 de stock. Podrá realizar la carga inicial autorizada una vez registrado.</small>
                        </div>

                        <div class="col-md-12">
                            <label for="description" class="form-label fw-bold">Descripción / Detalles</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Observaciones sobre especificaciones del lubricante, viscosidad, etc.">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Producto</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
