@extends('layouts.app')

@section('title', 'Mi Perfil — Lubricantes «El Cisne»')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body text-center p-4">
                <div class="fs-1 text-primary mb-2">👤</div>
                <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ $user->email }}</p>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-{{ $user->isAdmin() ? 'primary' : 'info' }} px-3 py-2">
                        Rol: {{ $user->role->label() }}
                    </span>
                    <span class="badge bg-{{ $user->active ? 'success' : 'danger' }} px-3 py-2">
                        Estado: {{ $user->active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>

                <hr>

                <div class="text-start small text-muted">
                    <p class="mb-1"><strong>Último acceso:</strong> {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Sin registros' }}</p>
                    <p class="mb-0"><strong>Fecha de registro:</strong> {{ $user->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mt-4">
            <div class="card-header bg-light fw-bold py-3">
                Mis Permisos Efectivos
            </div>
            <div class="card-body p-3">
                @if($user->isAdmin())
                    <p class="text-success small mb-0 fw-semibold">
                        <i class="bi bi-shield-check me-1"></i> Como administrador posee acceso total a todos los módulos.
                    </p>
                @else
                    @if($user->permissions->isEmpty())
                        <p class="text-muted small mb-0">No tiene permisos específicos asignados.</p>
                    @else
                        <ul class="list-group list-group-flush small">
                            @foreach($user->permissions as $perm)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span>{{ $perm->name }}</span>
                                    <code class="text-muted">{{ $perm->key }}</code>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white fw-bold py-3 border-0">
                Información Personal
            </div>
            <div class="card-body p-4 border-top">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Nombre Completo</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white fw-bold py-3 border-0">
                Cambiar Contraseña
            </div>
            <div class="card-body p-4 border-top">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">Contraseña Actual</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">Nueva Contraseña</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required minlength="10">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="10">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-warning">Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 border-danger">
            <div class="card-header bg-white text-danger fw-bold py-3 border-0">
                Seguridad de Sesiones
            </div>
            <div class="card-body p-4 border-top">
                <p class="text-muted small">
                    Si sospecha que su cuenta fue abierta en otro dispositivo, puede cerrar todas las demás sesiones excepto la actual.
                </p>
                <form method="POST" action="{{ route('profile.logout-others') }}" onsubmit="return confirm('¿Está seguro de cerrar las demás sesiones?');">
                    @csrf
                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <input type="password" class="form-control @error('current_password', 'logoutOtherDevices') is-invalid @enderror" name="current_password" placeholder="Confirme su contraseña actual" required>
                        </div>
                        <div class="col-md-5 text-end">
                            <button type="submit" class="btn btn-outline-danger w-100">Cerrar otras sesiones</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
