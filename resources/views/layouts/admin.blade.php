<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') | Encuentra tu Rincón Guanaco</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">

    @include('partials.auth-guard')

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>
    <div class="d-lg-flex min-vh-100">

        {{-- Sidebar: fija desde lg, menú lateral en móvil --}}
        <aside id="sidebar" class="sidebar offcanvas-lg offcanvas-start bg-primary text-white" tabindex="-1"
            aria-labelledby="sidebarLabel">
            <div class="offcanvas-header d-lg-none">
                <span id="sidebarLabel" class="visually-hidden">Menú principal</span>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas"
                    data-bs-target="#sidebar" aria-label="Cerrar"></button>
            </div>

            <div class="offcanvas-body d-block p-0">
                <div class="d-flex align-items-center gap-2 px-3 py-4 border-bottom border-light border-opacity-10">
                    <img src="{{ asset('images/logo.png') }}" alt="" height="40">
                    <div class="lh-1">
                        <div class="h5 mb-1 text-white">Rincón <span class="text-accent">Guanaco</span></div>
                        <small>Admin</small>
                    </div>
                </div>

                @include('partials.nav')
            </div>
        </aside>

        {{-- Columna derecha: barra superior + contenido --}}
        <div class="flex-grow-1" style="min-width: 0;">
            <header class="bg-white border-bottom px-3 px-lg-4 py-3 d-flex align-items-center justify-content-between">
                <button class="btn btn-outline-primary d-lg-none" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Abrir menú">
                    <i class="bi bi-list"></i>
                </button>

                <div class="dropdown ms-auto">
                    <button class="btn border-0 p-0 d-flex align-items-center gap-2" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <span id="userInitials"
                            class="rounded-circle bg-accent text-white fw-semibold d-flex align-items-center justify-content-center"
                            style="width: 40px; height: 40px;">--</span>
                        <span id="userName" class="d-none d-sm-inline h6 mb-0">Cargando...</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button type="button" id="logoutBtn" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                            </button>
                        </li>
                    </ul>
                </div>
            </header>

            <main class="p-3 p-lg-4">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>

</html>
