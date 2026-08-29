@extends('layouts.app')

@section('title', 'Editar Entrada de Stock')

@section('content')
<div class="container-fluid px-4">
    <h2 class="mt-4">Editar Borrador de Entrada</h2>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('stock-entries.index') }}">Entradas</a></li>
        <li class="breadcrumb-item"><a href="{{ route('stock-entries.show', $stockEntry) }}">#{{ $stockEntry->id }}</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('stock-entries.update', $stockEntry) }}" method="POST" id="entryForm">
                @csrf
                @method('PUT')
                
                <h5 class="mb-3">Información General</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Fecha de Entrada <span class="text-danger">*</span></label>
                        <input type="date" name="entry_date" class="form-control" value="{{ old('entry_date', $stockEntry->entry_date->format('Y-m-d')) }}" required max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Proveedor</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">Ninguno</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $stockEntry->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Referencia / Factura</label>
                        <input type="text" name="reference" class="form-control" value="{{ old('reference', $stockEntry->reference) }}" maxlength="200">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Notas Adicionales</label>
                    <textarea name="notes" class="form-control" rows="2" maxlength="1000">{{ old('notes', $stockEntry->notes) }}</textarea>
                </div>

                <hr>
                
                <h5 class="mb-3 d-flex justify-content-between align-items-center">
                    Detalle de Productos
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn">+ Agregar Producto</button>
                </h5>

                <div class="table-responsive">
                    <table class="table table-bordered" id="detailsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%">Producto <span class="text-danger">*</span></th>
                                <th style="width: 15%">Cant. <span class="text-danger">*</span></th>
                                <th style="width: 15%">Costo U. <span class="text-danger">*</span></th>
                                <th style="width: 20%">Total</th>
                                <th style="width: 10%" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="detailsBody">
                            <!-- Populated by JS -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total Entrada:</th>
                                <th id="grandTotal" class="fs-5 text-primary">0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('stock-entries.show', $stockEntry) }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Guardar Borrador</button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="rowTemplate">
    <tr>
        <td>
            <select class="form-select product-select" required>
                <option value="">Seleccione un producto</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }} ({{ $product->unit }})</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" step="0.001" min="0.001" class="form-control qty-input" required>
        </td>
        <td>
            <input type="number" step="0.01" min="0.00" class="form-control cost-input" required>
        </td>
        <td class="align-middle line-total">
            0.00
        </td>
        <td class="text-center align-middle">
            <button type="button" class="btn btn-sm btn-outline-danger remove-btn">X</button>
        </td>
    </tr>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tbody = document.getElementById('detailsBody');
        const addBtn = document.getElementById('addLineBtn');
        const template = document.getElementById('rowTemplate');
        const grandTotalEl = document.getElementById('grandTotal');
        const form = document.getElementById('entryForm');

        function updateGrandTotal() {
            let grandTotal = 0;
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                const total = qty * cost;
                row.querySelector('.line-total').textContent = total.toFixed(2);
                grandTotal += total;
            });
            grandTotalEl.textContent = grandTotal.toFixed(2);
        }

        function reindexNames() {
            const rows = tbody.querySelectorAll('tr');
            rows.forEach((row, index) => {
                row.querySelector('.product-select').name = `details[${index}][product_id]`;
                row.querySelector('.qty-input').name = `details[${index}][quantity]`;
                row.querySelector('.cost-input').name = `details[${index}][unit_cost]`;
            });
        }

        function addRow(productId = '', qty = '', cost = '') {
            if (tbody.querySelectorAll('tr').length >= 200) {
                alert('No se pueden agregar más de 200 líneas.');
                return;
            }
            
            const clone = template.content.cloneNode(true);
            const tr = clone.querySelector('tr');
            
            const pSelect = tr.querySelector('.product-select');
            const qInput = tr.querySelector('.qty-input');
            const cInput = tr.querySelector('.cost-input');

            pSelect.value = productId;
            qInput.value = qty;
            cInput.value = cost;

            pSelect.addEventListener('change', updateGrandTotal);
            qInput.addEventListener('input', updateGrandTotal);
            cInput.addEventListener('input', updateGrandTotal);
            
            tr.querySelector('.remove-btn').addEventListener('click', function() {
                tr.remove();
                reindexNames();
                updateGrandTotal();
            });

            tbody.appendChild(tr);
            reindexNames();
            updateGrandTotal();
        }

        addBtn.addEventListener('click', () => addRow());
        
        form.addEventListener('submit', function(e) {
            const rows = tbody.querySelectorAll('tr');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un producto a la entrada.');
                return;
            }
            
            const productIds = new Set();
            let hasDuplicates = false;
            
            rows.forEach(row => {
                const pId = row.querySelector('.product-select').value;
                if(productIds.has(pId)) {
                    hasDuplicates = true;
                }
                productIds.add(pId);
            });
            
            if (hasDuplicates) {
                e.preventDefault();
                alert('Existen productos duplicados en la entrada. Consolide las cantidades.');
                return;
            }

            document.getElementById('saveBtn').disabled = true;
        });

        // Load existing details
        const existingDetails = @json($stockEntry->details->map(fn($d) => ['product_id' => $d->product_id, 'quantity' => (float)$d->quantity, 'unit_cost' => (float)$d->unit_cost]));
        
        if (existingDetails.length > 0) {
            existingDetails.forEach(d => {
                addRow(d.product_id, d.quantity, d.unit_cost);
            });
        } else {
            addRow();
        }
    });
</script>
@endsection
