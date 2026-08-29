<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Lubricantes El Cisne'))</title>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('layouts.partials.navbar')

    <main class="py-4">
        <div class="container">
            <x-flash-messages />
            <x-validation-errors />

            @yield('content')
        </div>
    </main>

    <footer class="footer-cisne text-center">
        <div class="container">
            <p class="mb-1">&copy; {{ date('Y') }} Lubricantes «El Cisne» — San Lorenzo, Esmeraldas, Ecuador.</p>
            <small class="text-muted">Sistema de gestión de inventario y ventas v0.1.0</small>
        </div>
    </footer>
</body>
</html>
