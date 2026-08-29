@extends('layouts.app')

@section('title', 'Detalle de Cliente — Lubricantes «El Cisne»')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 fw-bold mb-0">Detalle de Cliente</h1>
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm">
                ← Volver al listado
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-muted">ID: #{{ $customer->id }}</span>
                @if($customer->active)
                    <span class="badge bg-success rounded-pill px-3">Activo</span>
                @else
                    <span class="badge bg-secondary rounded-pill px-3">Inactivo</span>
                @endif
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Nombre / Razón Social</label>
                    <p class="fs-5 fw-bold text-dark mb-0">{{ $customer->name }}</p>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Identificación / Cédula / RUC</label>
                        <p class="mb-0"><code>{{ $customer->identification ?? 'No registrada' }}</code></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Teléfono</label>
                        <p class="mb-0">{{ $customer->phone ?? 'No registrado' }}</p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Correo Electrónico</label>
                        <p class="mb-0">{{ $customer->email ?? 'No registrado' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Dirección</label>
                        <p class="mb-0">{{ $customer->address ?? 'No registrada' }}</p>
                    </div>
                </div>

                <div class="row g-3 pt-3 border-top text-muted small">
                    <div class="col-6">
                        <strong>Fecha de Creación:</strong><br>
                        {{ $customer->created_at ? $customer->created_at->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                    <div class="col-6">
                        <strong>Última Actualización:</strong><br>
                        {{ $customer->updated_at ? $customer->updated_at->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                </div>
            </div>
            @can('customers.manage')
                <div class="card-footer bg-light py-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-sm">Editar Cliente</a>
                    <form method="POST" action="{{ route('customers.toggle-status', $customer) }}" class="d-inline" onsubmit="return confirm('¿Está seguro de {{ $customer->active ? 'desactivar' : 'activar' }} este cliente?');">
                        @csrf
                        <button type="submit" class="btn {{ $customer->active ? 'btn-outline-warning' : 'btn-outline-success' }} btn-sm">
                            {{ $customer->active ? 'Desactivar' : 'Activar' }}
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection
