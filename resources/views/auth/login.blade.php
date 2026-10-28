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

                <div class="text-center mb-4 d-lg-none">
                    <img src="{{ asset('images/logo.png') }}" alt="" height="80">
                </div>

                <h1 class="mb-1">Bienvenido de nuevo</h1>
                <p class="text-secondary mb-4">Inicia sesión para continuar explorando Rincón Guanaco</p>

                <form id="loginForm" novalidate>

                    <div id="loginError" class="alert alert-danger d-none" role="alert"></div>

                    {{-- Usuario --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Usuario</label>
                        <div class="input-group border border-dark">
                            <span class="input-group-text border-0">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="email" id="email" name="email" class="form-control border-0"
                                autocomplete="username" required autofocus>
                        </div>
                    </div>

                    {{-- Contraseña --}}
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group border border-dark">
                            <span class="input-group-text border-0">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" class="form-control border-0"
                                autocomplete="current-password" required>
                            <button type="button" id="togglePassword" class="btn border-0" aria-label="Mostrar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="loginBtn" class="btn btn-accent w-100 py-2">
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

{{-- Si ya hay sesión válida, manda directo al dashboard --}}
@push('head')
    <script>
        (async () => {
            const token = localStorage.getItem('admin_token');
            if (!token) return;

            document.documentElement.style.visibility = 'hidden';

            try {
                const res = await fetch(@json(url('/api/me')), {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                });

                if (res.ok) {
                    window.location.replace(@json(route('admin.dashboard')));
                    return;
                }

                // Solo un 401 significa que el token ya no sirve
                if (res.status === 401) {
                    localStorage.removeItem('admin_token');
                    localStorage.removeItem('admin_user');
                }
            } catch (e) {
                console.error('Verificación de sesión:', e);
            }

            document.documentElement.style.visibility = '';
        })();
    </script>
@endpush

@push('scripts')
    <script>
        // Si ya hay sesión guardada, directo al dashboard (el guard del panel valida el token)
        if (localStorage.getItem('admin_token')) {
            window.location.replace(@json(route('admin.dashboard')));
        }

        const form = document.getElementById('loginForm');
        const btn = document.getElementById('loginBtn');
        const errorBox = document.getElementById('loginError');
        const toggle = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        // Mostrar / ocultar contraseña
        toggle.addEventListener('click', () => {
            const show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            toggle.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            toggle.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });

        const showError = (msg) => {
            errorBox.textContent = msg;
            errorBox.classList.remove('d-none');
        };

        const setLoading = (loading) => {
            btn.disabled = loading;
            btn.innerHTML = loading ?
                '<span class="spinner-border spinner-border-sm me-2"></span>Ingresando...' :
                'Iniciar Sesión <i class="bi bi-arrow-right ms-2"></i>';
        };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorBox.classList.add('d-none');
            setLoading(true);

            try {
                const res = await fetch(@json(url('/api/admin/login')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: form.elements.email.value,
                        password: form.elements.password.value,
                    }),
                });

                const data = await res.json().catch(() => ({}));

                if (res.status === 422) {
                    showError(Object.values(data.errors ?? {}).flat().join(' ') || 'Datos no válidos.');
                    return;
                }
                if (res.status === 429) {
                    showError('Demasiados intentos. Espera un minuto e inténtalo de nuevo.');
                    return;
                }
                if (!res.ok) {
                    showError(data.message ?? `Error del servidor (${res.status}).`);
                    return;
                }

                localStorage.setItem('admin_token', data.token);
                localStorage.setItem('admin_user', JSON.stringify(data.user));
                window.location.href = @json(route('admin.dashboard'));
            } catch (err) {
                console.error('Login:', err);
                showError('No se pudo conectar con el servidor.');
            } finally {
                setLoading(false);
            }
        });
    </script>
@endpush
