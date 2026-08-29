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
                {{-- Navegación autenticada preparada para K-002 --}}
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="#">Panel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Inventario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Ventas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Reportes</a>
                    </li>
                @endauth
            </ul>

            <div class="d-flex align-items-center">
                {{-- Estructura de usuario/autenticación preparada para K-002 --}}
                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name ?? 'Usuario' }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenu">
                            <li><a class="dropdown-menu-item" href="#">Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') ?? '#' }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    {{-- Espacio para enlace a Login en K-002 --}}
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm px-3">Iniciar Sesión</a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</nav>
