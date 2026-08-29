@extends('layouts.app')

@section('title', 'Administración de Usuarios — Lubricantes «El Cisne»')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Gestión de Usuarios</h2>
        <p class="text-muted mb-0">Administración de cuentas, roles y asignación de permisos.</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> Crear Nuevo Usuario
    </a>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('users.index') }}" class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o correo..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">Todos los Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                    <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Empleado</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos los Estados</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                @if(request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">X</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $user->name }}</div>
                                <div class="text-muted small">{{ $user->email }}</div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->isAdmin() ? 'primary' : 'info' }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->active ? 'success' : 'danger' }}">
                                    {{ $user->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary" title="Ver detalle">
                                        Ver
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary" title="Editar">
                                        Editar
                                    </a>
                                    @if(Auth::id() !== $user->id)
                                        <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="d-inline" onsubmit="return confirm('¿Confirma cambiar el estado de este usuario?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-{{ $user->active ? 'warning' : 'success' }}" title="{{ $user->active ? 'Desactivar' : 'Activar' }}">
                                                {{ $user->active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('users.reset-password', $user) }}" class="d-inline" onsubmit="return confirm('¿Confirma restablecer la contraseña de este usuario?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger" title="Restablecer clave">
                                            Clave
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No se encontraron usuarios que coincidan con el criterio de búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
