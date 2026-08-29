@extends('layouts.app')

@section('title', 'Inicio — Lubricantes «El Cisne»')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-body p-5 bg-white">
                <div class="text-center mb-4">
                    <span class="display-1">🛢️</span>
                    <h1 class="h2 fw-bold text-dark mt-2">Lubricantes «El Cisne»</h1>
                    <p class="lead text-muted">Sistema de Gestión de Inventario y Ventas</p>
                    <span class="badge bg-primary px-3 py-2 fs-6">San Lorenzo — Esmeraldas</span>
                </div>

                <hr class="my-4">

                <div class="row g-4 mt-2">
                    <div class="col-md-4">
                        <div class="card h-100 border-light bg-light">
                            <div class="card-body text-center p-4">
                                <div class="fs-1 text-primary mb-3">📦</div>
                                <h5 class="card-title fw-semibold">Control de Stock</h5>
                                <p class="card-text text-secondary small">Gestión precisa de existencias, entradas, ajustes y alertas de niveles mínimos.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-light bg-light">
                            <div class="card-body text-center p-4">
                                <div class="fs-1 text-primary mb-3">🧾</div>
                                <h5 class="card-title fw-semibold">Ventas y Comprobantes</h5>
                                <p class="card-text text-secondary small">Registro de ventas, emisión de comprobantes internos y reversión trazable.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-light bg-light">
                            <div class="card-body text-center p-4">
                                <div class="fs-1 text-primary mb-3">📊</div>
                                <h5 class="card-title fw-semibold">Trazabilidad y Reportes</h5>
                                <p class="card-text text-secondary small">Historial inmutable de movimientos, Kardex y reportes exportables.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-5 mb-0 d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div>
                        <strong>Estado del Entorno:</strong> Fase K-001 completada exitosamente. Entorno inicializado y verificado.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
