@extends('layouts.app')

@section('title', 'Kardex de Inventario')

@section('content')
<div class="container-fluid px-4">
    <h2 class="mt-4">Kardex de Inventario</h2>
    
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Generar Kardex</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.kardex.report') }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Producto <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                <option value="">Seleccione un producto</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->sku }} - {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicial <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', date('Y-m-01')) }}" required>
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Final <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', date('Y-m-t')) }}" required>
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">Generar Kardex</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
