@extends('layouts.app')

@section('title', 'Iniciar Sesión — Lubricantes «El Cisne»')

@section('content')
<div class="row justify-content-center align-items-center py-5">
    <div class="col-md-6 col-lg-5 col-xl-4">
        <div class="card border-0 shadow-lg rounded-3">
            <div class="card-header bg-primary text-white text-center py-4 rounded-top">
                <span class="fs-1">🛢️</span>
                <h4 class="fw-bold mb-0">Lubricantes «El Cisne»</h4>
                <small class="text-white-50">Acceso al Sistema de Inventario</small>
            </div>
            <div class="card-body p-4">
                <x-flash-messages />
                <x-validation-errors />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="correo@ejemplo.com">
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                            Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light text-center py-3 text-muted small">
                Sistema exclusivo para personal autorizado
            </div>
        </div>
    </div>
</div>
@endsection
