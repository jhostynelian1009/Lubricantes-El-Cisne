@extends('layouts.app')

@section('title', 'Nuevo Ajuste de Inventario')

@section('content')
<div class="container-fluid px-4">
    <h2 class="mt-4">Nuevo Ajuste de Inventario</h2>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Productos</a></li>
        <li class="breadcrumb-item active">Ajuste</li>
    </ol>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Detalles del Ajuste</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.adjustments.store') }}" method="POST" id="adjustmentForm">
                        @csrf

                        <div class="mb-3">
                            <label for="product_id" class="form-label">Producto <span class="text-danger">*</span></label>
                            <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                                <option value="">Seleccione un producto</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                        data-stock="{{ $product->current_stock }}" 
                                        data-unit="{{ $product->unit }}"
                                        {{ old('product_id', request('product_id')) == $product->id ? 'selected' : '' }}>
                                        {{ $product->sku }} - {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Tipo de Ajuste <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Seleccione el tipo</option>
                                    <option value="adjustment_in" {{ old('type') == 'adjustment_in' ? 'selected' : '' }}>Incremento (+)</option>
                                    <option value="adjustment_out" {{ old('type') == 'adjustment_out' ? 'selected' : '' }}>Disminución (-)</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" min="0.001" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}" required>
                                @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Motivo del Ajuste <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3" required maxlength="500">{{ old('reason') }}</textarea>
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="alert alert-info mt-4">
                            <h6 class="alert-heading fw-bold">Vista Previa de Saldo</h6>
                            <hr>
                            <p class="mb-1">Saldo Actual: <strong id="currentStockPreview">0.000</strong> <span class="unitPreview"></span></p>
                            <p class="mb-0">Saldo Resultante: <strong id="finalStockPreview" class="fs-5 text-primary">0.000</strong> <span class="unitPreview"></span></p>
                            <div id="negativeStockWarning" class="text-danger fw-bold mt-2 d-none">
                                ⚠️ Advertencia: El ajuste resultará en un stock negativo, lo cual no está permitido.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">Aplicar Ajuste</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const productSelect = document.getElementById('product_id');
        const typeSelect = document.getElementById('type');
        const quantityInput = document.getElementById('quantity');
        const currentStockPreview = document.getElementById('currentStockPreview');
        const finalStockPreview = document.getElementById('finalStockPreview');
        const unitPreviews = document.querySelectorAll('.unitPreview');
        const negativeStockWarning = document.getElementById('negativeStockWarning');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('adjustmentForm');

        function updatePreview() {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            if (!selectedOption.value) {
                currentStockPreview.textContent = '0.000';
                finalStockPreview.textContent = '0.000';
                unitPreviews.forEach(el => el.textContent = '');
                negativeStockWarning.classList.add('d-none');
                submitBtn.disabled = true;
                return;
            }

            const currentStock = parseFloat(selectedOption.dataset.stock) || 0;
            const unit = selectedOption.dataset.unit || '';
            const type = typeSelect.value;
            const quantity = parseFloat(quantityInput.value) || 0;

            let finalStock = currentStock;
            if (type === 'adjustment_in') {
                finalStock = currentStock + quantity;
            } else if (type === 'adjustment_out') {
                finalStock = currentStock - quantity;
            }

            currentStockPreview.textContent = currentStock.toFixed(3);
            finalStockPreview.textContent = finalStock.toFixed(3);
            unitPreviews.forEach(el => el.textContent = unit);

            if (finalStock < 0) {
                negativeStockWarning.classList.remove('d-none');
                finalStockPreview.classList.replace('text-primary', 'text-danger');
                submitBtn.disabled = true;
            } else {
                negativeStockWarning.classList.add('d-none');
                finalStockPreview.classList.replace('text-danger', 'text-primary');
                submitBtn.disabled = quantity <= 0 || !type;
            }
        }

        productSelect.addEventListener('change', updatePreview);
        typeSelect.addEventListener('change', updatePreview);
        quantityInput.addEventListener('input', updatePreview);

        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
        });

        // Init preview on load
        updatePreview();
    });
</script>
@endsection
