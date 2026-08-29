@extends('layouts.app')

@section('title', 'Punto de Venta (POS) — Lubricantes «El Cisne»')

@section('content')
<div class="container-fluid px-4 py-3" id="posContainer">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark">🛒 Punto de Venta (POS)</h1>
            <p class="text-muted small mb-0">Registre una nueva venta o prepare un borrador</p>
        </div>
        <div>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">📋 Ver Historial</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <form id="posForm" method="POST" action="{{ route('sales.store') }}">
        @csrf
        <div class="row g-3">
            {{-- Panel Izquierdo: Selección de Cliente y Búsqueda de Productos --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <label for="customer_id" class="form-label fw-semibold">Cliente (Opcional)</label>
                        <select name="customer_id" id="customer_id" class="form-select form-select-lg mb-3">
                            <option value="">-- Consumidor Final --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->identification ?? 'S/N' }})
                                </option>
                            @endforeach
                        </select>

                        <label for="productSearch" class="form-label fw-semibold">Buscar Producto (Nombre, SKU o Código de Barras)</label>
                        <div class="input-group input-group-lg position-relative mb-2">
                            <span class="input-group-text bg-white">🔍</span>
                            <input type="text" id="productSearch" class="form-control" placeholder="Escriba o escanee un código de barras..." autocomplete="off">
                        </div>
                        <div id="searchResults" class="list-group shadow-sm position-absolute w-100 z-3 d-none" style="max-height: 250px; overflow-y: auto;"></div>
                    </div>
                </div>

                {{-- Tabla del Carrito --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold">📦 Detalle de la Venta</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="cartTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th style="width: 100px;">Unidad</th>
                                    <th style="width: 100px;">Stock</th>
                                    <th style="width: 120px;">Cantidad</th>
                                    <th class="text-end" style="width: 120px;">Precio Unit.</th>
                                    <th class="text-end" style="width: 130px;">Subtotal</th>
                                    <th class="text-center" style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="cartBody">
                                <tr id="emptyCartRow">
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No hay productos agregados. Utilice el buscador para agregar productos.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Panel Derecho: Resumen y Acciones --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">💵 Resumen de Venta</h5>

                        <div class="d-flex justify-content-between fs-5 mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span id="posSubtotal" class="fw-bold">$0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fs-3 fw-bold text-success mb-4">
                            <span>TOTAL:</span>
                            <span id="posTotal">$0.00</span>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" id="btnSaveDraft" class="btn btn-outline-primary btn-lg fw-semibold">
                                💾 Guardar Borrador
                            </button>
                            <button type="button" id="btnConfirmSale" class="btn btn-success btn-lg fw-bold" data-bs-toggle="modal" data-bs-target="#confirmModal" disabled>
                                ✅ Confirmar Venta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Container for hidden form inputs --}}
        <div id="hiddenInputsContainer"></div>
    </form>
</div>

{{-- Modal de Confirmación --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="confirmModalLabel">Confirmar Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-4">
                <p class="fs-5 text-center mb-2">¿Está seguro de confirmar esta venta por <strong id="modalTotalAmount">$0.00</strong>?</p>
                <div class="alert alert-warning mb-0 small">
                    ⚠️ <strong>Atención:</strong> Al confirmar, se descontarán las existencias del inventario de forma irreversible y se generará el comprobante interno.
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnExecuteConfirm" class="btn btn-success fw-bold px-4">
                    Confirmar e Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('productSearch');
    const searchResults = document.getElementById('searchResults');
    const cartBody = document.getElementById('cartBody');
    const emptyCartRow = document.getElementById('emptyCartRow');
    const posSubtotal = document.getElementById('posSubtotal');
    const posTotal = document.getElementById('posTotal');
    const modalTotalAmount = document.getElementById('modalTotalAmount');
    const btnConfirmSale = document.getElementById('btnConfirmSale');
    const btnExecuteConfirm = document.getElementById('btnExecuteConfirm');
    const posForm = document.getElementById('posForm');
    const hiddenInputsContainer = document.getElementById('hiddenInputsContainer');

    let cart = []; // Array of { id, name, sku, unit, stock, price, quantity }

    let debounceTimer;
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const query = searchInput.value.trim();
        if (query.length === 0) {
            searchResults.classList.add('d-none');
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/sales/products/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length === 0) {
                        searchResults.innerHTML = '<div class="list-group-item text-muted">No se encontraron productos activos</div>';
                    } else {
                        data.forEach(prod => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                            btn.innerHTML = `
                                <div>
                                    <strong class="d-block">${escapeHtml(prod.name)}</strong>
                                    <small class="text-muted">SKU: ${escapeHtml(prod.sku)} | Stock: ${prod.current_stock} ${prod.unit}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill fs-6">$${parseFloat(prod.sale_price).toFixed(2)}</span>
                            `;
                            btn.addEventListener('click', () => {
                                addToCart(prod);
                                searchInput.value = '';
                                searchResults.classList.add('d-none');
                            });
                            searchResults.appendChild(btn);
                        });
                    }
                    searchResults.classList.remove('d-none');
                });
        }, 250);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('d-none');
        }
    });

    function addToCart(product) {
        const existing = cart.find(item => item.id === product.id);
        if (existing) {
            existing.quantity = (parseFloat(existing.quantity) + 1).toFixed(3);
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                sku: product.sku,
                unit: product.unit,
                stock: product.current_stock,
                price: parseFloat(product.sale_price).toFixed(2),
                quantity: '1.000'
            });
        }
        renderCart();
    }

    function renderCart() {
        if (cart.length === 0) {
            cartBody.innerHTML = '';
            cartBody.appendChild(emptyCartRow);
            btnConfirmSale.disabled = true;
            updateTotals();
            return;
        }

        cartBody.innerHTML = '';
        let subtotal = 0;

        cart.forEach((item, index) => {
            const qty = parseFloat(item.quantity) || 0;
            const price = parseFloat(item.price) || 0;
            const lineTotal = qty * price;
            subtotal += lineTotal;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <strong class="d-block text-dark">${escapeHtml(item.name)}</strong>
                    <small class="text-muted">SKU: ${escapeHtml(item.sku)}</small>
                </td>
                <td><span class="badge bg-light text-dark border">${escapeHtml(item.unit)}</span></td>
                <td><span class="fw-semibold ${parseFloat(item.stock) <= 0 ? 'text-danger' : 'text-success'}">${item.stock}</span></td>
                <td>
                    <input type="number" step="0.001" min="0.001" class="form-control form-control-sm qty-input" data-index="${index}" value="${item.quantity}">
                </td>
                <td class="text-end fw-semibold">$${price.toFixed(2)}</td>
                <td class="text-end fw-bold text-primary">$${lineTotal.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-btn" data-index="${index}">❌</button>
                </td>
            `;
            cartBody.appendChild(tr);
        });

        btnConfirmSale.disabled = cart.length === 0;
        updateTotals();
        syncHiddenInputs();
    }

    cartBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('qty-input')) {
            const index = e.target.getAttribute('data-index');
            let newQty = parseFloat(e.target.value);
            if (isNaN(newQty) || newQty <= 0) newQty = 0.001;
            cart[index].quantity = newQty.toFixed(3);
            renderCart();
        }
    });

    cartBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-btn')) {
            const index = e.target.getAttribute('data-index');
            cart.splice(index, 1);
            renderCart();
        }
    });

    function updateTotals() {
        let subtotal = 0;
        cart.forEach(item => {
            subtotal += (parseFloat(item.quantity) || 0) * (parseFloat(item.price) || 0);
        });
        const formatted = `$${subtotal.toFixed(2)}`;
        posSubtotal.textContent = formatted;
        posTotal.textContent = formatted;
        modalTotalAmount.textContent = formatted;
    }

    function syncHiddenInputs() {
        hiddenInputsContainer.innerHTML = '';
        cart.forEach((item, index) => {
            const prodInput = document.createElement('input');
            prodInput.type = 'hidden';
            prodInput.name = `details[${index}][product_id]`;
            prodInput.value = item.id;

            const qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = `details[${index}][quantity]`;
            qtyInput.value = item.quantity;

            hiddenInputsContainer.appendChild(prodInput);
            hiddenInputsContainer.appendChild(qtyInput);
        });
    }

    btnExecuteConfirm.addEventListener('click', function() {
        btnExecuteConfirm.disabled = true;
        btnExecuteConfirm.textContent = 'Procesando...';

        // Submit form via fetch to store draft then confirm
        syncHiddenInputs();
        const formData = new FormData(posForm);

        fetch(posForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.sale) {
                // Confirm the sale
                return fetch(`/sales/${data.sale.id}/confirm`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
            } else {
                throw new Error(data.message || 'Error al guardar el borrador');
            }
        })
        .then(res => res.json())
        .then(confirmData => {
            if (confirmData.success && confirmData.redirect) {
                window.location.href = confirmData.redirect;
            } else {
                alert(confirmData.message || 'Error al confirmar la venta.');
                btnExecuteConfirm.disabled = false;
                btnExecuteConfirm.textContent = 'Confirmar e Imprimir';
            }
        })
        .catch(err => {
            alert('Ocurrió un error al procesar la venta: ' + err.message);
            btnExecuteConfirm.disabled = false;
            btnExecuteConfirm.textContent = 'Confirmar e Imprimir';
        });
    });

    function escapeHtml(str) {
        return (str || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }
});
</script>
@endsection
