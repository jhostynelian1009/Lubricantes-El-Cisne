@extends('layouts.app')

@section('title', 'Gestión de Clientes — Lubricantes «El Cisne»')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Catálogo de Clientes</h1>
        <p class="text-muted mb-0">Gestión de clientes registrados para registro de ventas.</p>
    </div>
    @can('customers.manage')
        <div>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                + Nuevo Cliente
            </a>
        </div>
    @endcan
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-3">
            <div class="col-12 col-md-6 col-lg-7">
                <label for="search" class="form-label visually-hidden">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, cédula/RUC, teléfono o correo...">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label for="status" class="form-label visually-hidden">Estado</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>
            <div class="col-12 col-md-2 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">✕</a>
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
                        <th scope="col" class="ps-4">Nombre / Razón Social</th>
                        <th scope="col">Identificación</th>
                        <th scope="col">Contacto</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $customer->name }}</td>
                            <td><code>{{ $customer->identification ?? 'Sin ID' }}</code></td>
                            <td class="small">
                                @if($customer->phone) <div>📞 {{ $customer->phone }}</div> @endif
                                @if($customer->email) <div>✉️ {{ $customer->email }}</div> @endif
                                @if(!$customer->phone && !$customer->email) <span class="text-muted">Sin datos</span> @endif
                            </td>
                            <td>
                                @if($customer->active)
                                    <span class="badge bg-success rounded-pill px-3">Activo</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-primary" title="Ver detalle">
                                        Ver
                                    </a>
                                    @can('customers.manage')
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-secondary" title="Editar">
                                            Editar
                                        </a>
                                        <form method="POST" action="{{ route('customers.toggle-status', $customer) }}" class="d-inline" onsubmit="return confirm('¿Está seguro de {{ $customer->active ? 'desactivar' : 'activar' }} este cliente?');">
                                            @csrf
                                            <button type="submit" class="btn {{ $customer->active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                {{ $customer->active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                @if(request()->anyFilled(['search', 'status']))
                                    <p class="mb-1">No se encontraron clientes que coincidan con los filtros aplicados.</p>
                                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-primary mt-2">Limpiar filtros</a>
                                @else
                                    <p class="mb-1">No hay clientes registrados en el sistema.</p>
                                    @can('customers.manage')
                                        <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary mt-2">+ Crear el primer cliente</a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($customers->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection
