<nav class="navbar navbar-expand-lg navbar-dark navbar-cisne">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ url('/') }}">
            <span class="fs-4 me-2">🛢️</span>
            <span>Lubricantes «El Cisne»</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Navegación principal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active fw-semibold' : '' }}" href="{{ url('/') }}">Inicio</a>
                </li>
                @auth
                    @canany(['categories.manage', 'suppliers.manage', 'customers.manage'])
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('categories*', 'suppliers*', 'customers*') ? 'active fw-semibold' : '' }}" href="#" id="catalogsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Catálogos
                            </a>
                            <ul class="dropdown-menu shadow" aria-labelledby="catalogsDropdown">
                                @can('categories.manage')
                                    <li><a class="dropdown-item {{ request()->is('categories*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Categorías</a></li>
                                @endcan
                                @can('suppliers.manage')
                                    <li><a class="dropdown-item {{ request()->is('suppliers*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">Proveedores</a></li>
                                @endcan
                                @can('customers.manage')
                                    <li><a class="dropdown-item {{ request()->is('customers*') ? 'active' : '' }}" href="{{ route('customers.index') }}">Clientes</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['inventory.view', 'products.manage', 'inventory.entries.create'])
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('products*', 'stock-entries*', 'inventory*') ? 'active fw-semibold' : '' }}" href="#" id="inventoryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Inventario
                            </a>
                            <ul class="dropdown-menu shadow" aria-labelledby="inventoryDropdown">
                                @canany(['inventory.view', 'products.manage'])
                                    <li><a class="dropdown-item {{ request()->is('products*') ? 'active' : '' }}" href="{{ route('products.index') }}">Productos</a></li>
                                @endcanany
                                @can('inventory.view')
                                    <li><a class="dropdown-item {{ request()->is('inventory/movements*') ? 'active' : '' }}" href="{{ route('inventory.movements.index') }}">Movimientos</a></li>
                                    <li><a class="dropdown-item {{ request()->is('inventory/kardex*') ? 'active' : '' }}" href="{{ route('inventory.kardex.form') }}">Kardex</a></li>
                                @endcan
                                @can('inventory.entries.create')
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item {{ request()->is('stock-entries*') ? 'active' : '' }}" href="{{ route('stock-entries.index') }}">Entradas de Stock</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @can('sales.create')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('sales*') ? 'active fw-semibold' : '' }}" href="#" id="salesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Ventas (POS)
                            </a>
                            <ul class="dropdown-menu shadow" aria-labelledby="salesDropdown">
                                <li><a class="dropdown-item {{ request()->is('sales/create') ? 'active' : '' }}" href="{{ route('sales.create') }}">Punto de Venta (POS)</a></li>
                                <li><a class="dropdown-item {{ request()->is('sales') && !request()->is('sales/create') ? 'active' : '' }}" href="{{ route('sales.index') }}">Historial de Ventas</a></li>
                            </ul>
                        </li>
                    @endcan

                    @can('users.manage')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('users*') ? 'active fw-semibold' : '' }}" href="{{ route('users.index') }}">Usuarios</a>
                        </li>
                    @endcan
                @endauth
            </ul>

            <div class="d-flex align-items-center">
                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name ?? 'Usuario' }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenu">
                            <li><a class="dropdown-item {{ request()->is('profile*') ? 'active' : '' }}" href="{{ route('profile.show') }}">Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm px-3">Iniciar Sesión</a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</nav>
