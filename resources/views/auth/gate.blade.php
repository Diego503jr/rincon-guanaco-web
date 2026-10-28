<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cargando...</title>
    <noscript>
        <meta http-equiv="refresh" content="0;url={{ route('login') }}">
    </noscript>
</head>

<body>
    <script>
        const token = localStorage.getItem('admin_token');
        window.location.replace(token ? @json(route('admin.dashboard')) : @json(route('login')));

        (async () => {
            const LOGIN = @json(route('login'));
            const DASHBOARD = @json(route('admin.dashboard'));

            const token = localStorage.getItem('admin_token');
            if (!token) {
                window.location.replace(LOGIN);
                return;
            }

            try {
                // Verifica que el token siga siendo válido
                const res = await fetch(@json(url('/api/me')), {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                });

                if (res.ok) {
                    window.location.replace(DASHBOARD);
                    return;
                }

                if (res.status === 401) {
                    localStorage.removeItem('admin_token');
                    localStorage.removeItem('admin_user');
                }

                // // Token vencido o inválido
                // localStorage.removeItem('admin_token');
                // localStorage.removeItem('admin_user');
            } catch (e) {
                // Sin conexión con el servidor: cae al login
            }

            window.location.replace(LOGIN);
        })();
    </script>
</body>

</html>
