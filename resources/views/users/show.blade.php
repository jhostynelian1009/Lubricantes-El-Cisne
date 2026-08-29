@extends('layouts.app')

@section('title', 'Detalle de Usuario — Lubricantes «El Cisne»')

@section('content')
<div class="row justify-content-center py-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Detalle de Usuario</h5>
                    <div>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm me-1">Editar</a>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small">Nombre Completo</label>
                        <div class="fs-5 fw-bold">{{ $user->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Correo Electrónico</label>
                        <div class="fs-5 fw-semibold">{{ $user->email }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Rol</label>
                        <div>
                            <span class="badge bg-{{ $user->isAdmin() ? 'primary' : 'info' }} fs-6">
                                {{ $user->role->label() }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Estado</label>
                        <div>
                            <span class="badge bg-{{ $user->active ? 'success' : 'danger' }} fs-6">
                                {{ $user->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Último Acceso</label>
                        <div class="fw-semibold">
                            {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Sin registros' }}
                        </div>
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold mb-3">Permisos Asignados</h6>
                @if($user->isAdmin())
                    <div class="alert alert-primary">
                        <i class="bi bi-shield-check me-1"></i> Este usuario es <strong>Administrador</strong> y tiene acceso total al sistema.
                    </div>
                @else
                    @if($user->permissions->isEmpty())
                        <div class="alert alert-light border">No tiene permisos específicos asignados.</div>
                    @else
                        <div class="row g-2">
                            @foreach($user->permissions as $perm)
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-light small">
                                        <div class="fw-semibold">{{ $perm->name }}</div>
                                        <code class="text-muted">{{ $perm->key }}</code>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
