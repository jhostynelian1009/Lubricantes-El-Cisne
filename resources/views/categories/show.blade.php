@extends('layouts.app')

@section('title', 'Detalle de Categoría — Lubricantes «El Cisne»')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 fw-bold mb-0">Detalle de Categoría</h1>
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm">
                ← Volver al listado
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-muted">ID: #{{ $category->id }}</span>
                @if($category->active)
                    <span class="badge bg-success rounded-pill px-3">Activo</span>
                @else
                    <span class="badge bg-secondary rounded-pill px-3">Inactivo</span>
                @endif
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Nombre</label>
                    <p class="fs-5 fw-bold text-dark mb-0">{{ $category->name }}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Descripción</label>
                    <p class="text-dark mb-0">{{ $category->description ?? 'Sin descripción ingresada.' }}</p>
                </div>

                <div class="row g-3 pt-3 border-top text-muted small">
                    <div class="col-6">
                        <strong>Fecha de Creación:</strong><br>
                        {{ $category->created_at ? $category->created_at->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                    <div class="col-6">
                        <strong>Última Actualización:</strong><br>
                        {{ $category->updated_at ? $category->updated_at->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                </div>
            </div>
            @can('categories.manage')
                <div class="card-footer bg-light py-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary btn-sm">Editar Categoría</a>
                    <form method="POST" action="{{ route('categories.toggle-status', $category) }}" class="d-inline" onsubmit="return confirm('¿Está seguro de {{ $category->active ? 'desactivar' : 'activar' }} esta categoría?');">
                        @csrf
                        <button type="submit" class="btn {{ $category->active ? 'btn-outline-warning' : 'btn-outline-success' }} btn-sm">
                            {{ $category->active ? 'Desactivar' : 'Activar' }}
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection
