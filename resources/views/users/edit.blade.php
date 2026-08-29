@extends('layouts.app')

@section('title', 'Editar Usuario — Lubricantes «El Cisne»')

@section('content')
<div class="row justify-content-center py-3">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white fw-bold py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Editar Usuario: {{ $user->name }}</h5>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">Volver al listado</a>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label fw-semibold">Rol de Usuario <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required onchange="togglePermissionsSection(this.value)">
                                <option value="employee" {{ old('role', $user->role->value) === 'employee' ? 'selected' : '' }}>Empleado</option>
                                <option value="admin" {{ old('role', $user->role->value) === 'admin' ? 'selected' : '' }}>Administrador</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado de la Cuenta</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $user->active ? '1' : '0') == '1' ? 'checked' : '' }} {{ Auth::id() === $user->id ? 'disabled' : '' }}>
                                <label class="form-check-label fw-semibold" for="active">Cuenta Activa</label>
                            </div>
                            @if(Auth::id() === $user->id)
                                <input type="hidden" name="active" value="1">
                                <div class="form-text text-muted">No puede desactivar su propia cuenta.</div>
                            @endif
                        </div>
                    </div>

                    <div id="permissions-section" class="border-top pt-4 mb-4">
                        <h6 class="fw-bold mb-2">Permisos Asignables al Empleado</h6>
                        <p class="text-muted small mb-3">Los administradores poseen acceso total automático. Seleccione los permisos específicos para este empleado:</p>

                        @php
                            $assignedKeys = old('permissions', $user->permissions->pluck('key')->toArray());
                        @endphp

                        <div class="row g-3">
                            @foreach($permissions as $permission)
                                <div class="col-md-6">
                                    <div class="card h-100 border-light bg-light">
                                        <div class="card-body p-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->key }}" id="perm_{{ $permission->id }}" {{ in_array($permission->key, $assignedKeys) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold text-dark" for="perm_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                                <div class="small text-muted">Clave: <code>{{ $permission->key }}</code></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePermissionsSection(role) {
        const section = document.getElementById('permissions-section');
        if (role === 'admin') {
            section.style.display = 'none';
        } else {
            section.style.display = 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        togglePermissionsSection(document.getElementById('role').value);
    });
</script>
@endsection
