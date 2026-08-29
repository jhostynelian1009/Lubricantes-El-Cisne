@extends('layouts.app')

@section('title', 'Cambiar Contraseña — Lubricantes «El Cisne»')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-warning text-dark py-3">
                <h5 class="fw-bold mb-0">Cambiar Contraseña Obligatoria</h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-4">
                    Su cuenta posee una contraseña temporal. Por motivos de seguridad, debe definir una nueva contraseña antes de continuar navegando.
                </p>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Nueva Contraseña</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required minlength="10">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">La contraseña debe tener al menos 10 caracteres.</div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="10">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            Actualizar Contraseña y Continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
