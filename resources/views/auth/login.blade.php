@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
    <div class="row g-0 w-100">

        {{-- Columna izquierda: imagen (solo desde lg) --}}
        <div class="col-lg-6 d-none d-lg-flex auth-hero"
            style="background-image: linear-gradient(rgba(13, 42, 77, .35), rgba(13, 42, 77, 0) 60%), url('{{ asset('images/login-bg.png') }}');">
            <div class="w-100 text-center text-white px-5 pt-5">
                <img src="{{ asset('images/logo.png') }}" alt="" height="120" class="mb-4">
                <h2 class="h3 text-white">Encuentra Tu <span class="text-accent">Rincón Guanaco</span></h2>
                <p class="mx-auto small" style="max-width: 320px;">
                    Descubre lugares increíbles, vive nuevas experiencias y conecta con lo mejor de nuestra tierra
                </p>
            </div>
        </div>

        {{-- Columna derecha: formulario --}}
        <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center p-4">
            <div class="w-100" style="max-width: 420px;">

                {{-- Logo visible solo en móvil/tablet, porque la imagen está oculta --}}
                <div class="text-center mb-4 d-lg-none">
                    <img src="{{ asset('images/logo.png') }}" alt="" height="80">
                </div>

                <h1 class="mb-1">Bienvenido de nuevo</h1>
                <p class="text-secondary mb-4">Inicia sesión para continuar explorando Rincón Guanaco</p>

                <form method="GET" action="{{ route('admin.dashboard') }}" novalidate>
                    @csrf

                    {{-- Usuario --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Usuario</label>
                        <div class="input-group has-validation border border-dark">
                            <span class="input-group-text border-0">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="email" id="email" name="email"
                                class="form-control border-primary @error('email') is-invalid @enderror border-0"
                                value="{{ old('email') }}" autocomplete="username" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Contraseña --}}
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group has-validation border border-dark">
                            <span class="input-group-text border-0">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" id="password" name="password"
                                class="form-control border-primary @error('password') is-invalid @enderror border-0"
                                autocomplete="current-password" required>
                            <button type="button" id="togglePassword" class="btn border-0" aria-label="Mostrar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent w-100 py-2">
                        Iniciar Sesión <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>

                <p class="text-center text-secondary small mt-4 mb-0">
                    Acceso exclusivo para administradores
                </p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const toggle = document.getElementById('togglePassword');
        const input = document.getElementById('password');

        toggle.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            toggle.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            toggle.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    </script>
@endpush
