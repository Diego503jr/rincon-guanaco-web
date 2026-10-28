<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Iniciar sesión') | Encuentra tu Rincón Guanaco</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">

    @stack('head')

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body class="d-flex flex-column min-vh-100">

    {{-- Header azul --}}
    <header class="bg-primary">
        <div class="container-fluid px-3 px-md-4 py-3 d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Rincón Guanaco" height="32">
                <span class="h5 mb-0 text-white">Rincón <span class="text-accent">Guanaco</span></span>
            </a>

            <span class="d-none d-md-inline h6 mb-0 text-white">Planifica · Reserva · Explora</span>
        </div>
    </header>

    <main class="flex-grow-1 d-flex">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
