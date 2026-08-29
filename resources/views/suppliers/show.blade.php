@extends('layouts.app')

@section('title', 'Detalle de Proveedor — Lubricantes «El Cisne»')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 fw-bold mb-0">Detalle de Proveedor</h1>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm">
                ← Volver al listado
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-muted">ID: #{{ $supplier->id }}</span>
                @if($supplier->active)
                    <span class="badge bg-success rounded-pill px-3">Activo</span>
                @else
                    <span class="badge bg-secondary rounded-pill px-3">Inactivo</span>
                @endif
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Nombre / Razón Social</label>
                    <p class="fs-5 fw-bold text-dark mb-0">{{ $supplier->name }}</p>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Identificación / RUC</label>
                        <p class="mb-0"><code>{{ $supplier->identification ?? 'No registrada' }}</code></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Teléfono</label>
                        <p class="mb-0">{{ $supplier->phone ?? 'No registrado' }}</p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Correo Electrónico</label>
                        <p class="mb-0">{{ $supplier->email ?? 'No registrado' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Dirección</label>
                        <p class="mb-0">{{ $supplier->address ?? 'No registrada' }}</p>
                    </div>
                </div>

                <div class="row g-3 pt-3 border-top text-muted small">
                    <div class="col-6">
                        <strong>Fecha de Creación:</strong><br>
                        {{ $supplier->created_at ? $supplier->created_at->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                    <div class="col-6">
                        <strong>Última Actualización:</strong><br>
                        {{ $supplier->updated_at ? $supplier->updated_at->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                </div>
            </div>
            @can('suppliers.manage')
                <div class="card-footer bg-light py-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary btn-sm">Editar Proveedor</a>
                    <form method="POST" action="{{ route('suppliers.toggle-status', $supplier) }}" class="d-inline" onsubmit="return confirm('¿Está seguro de {{ $supplier->active ? 'desactivar' : 'activar' }} este proveedor?');">
                        @csrf
                        <button type="submit" class="btn {{ $supplier->active ? 'btn-outline-warning' : 'btn-outline-success' }} btn-sm">
                            {{ $supplier->active ? 'Desactivar' : 'Activar' }}
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection
