<script>
    (() => {
        const LOGIN_URL = @json(route('login'));
        const API_URL = @json(url('/api'));
        const token = localStorage.getItem('admin_token');

        const goLogin = () => window.location.replace(LOGIN_URL);
        const clearSession = () => {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
        };

        if (!token) {
            goLogin();
            return;
        }

        // Oculta la página hasta confirmar que el token sigue vigente
        const root = document.documentElement;
        root.style.visibility = 'hidden';

        // Uso: const res = await api('/destinations'); const data = await res.json();
        window.api = async (path, options = {}) => {
            const res = await fetch(API_URL + path, {
                ...options,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    ...(options.headers || {}),
                },
            });
            if (res.status === 401) { // token vencido o inválido
                clearSession();
                goLogin();
            }
            return res;
        };

        // Validación al cargar / refrescar
        fetch(API_URL + '/me', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then((res) => {
                if (res.status === 401) {
                    clearSession();
                    goLogin();
                    return;
                }
                root.style.visibility = ''; // token válido: se muestra el panel
            })
            .catch(() => {
                root.style.visibility = ''; // sin conexión: no dejar la pantalla en blanco
            });

        // Si se cierra sesión en otra pestaña, esta también sale
        window.addEventListener('storage', (e) => {
            if (e.key === 'admin_token' && !e.newValue) goLogin();
        });

        // Botón "Atrás" después de cerrar sesión (página servida desde caché)
        window.addEventListener('pageshow', (e) => {
            if (e.persisted && !localStorage.getItem('admin_token')) goLogin();
        });

        // ===== Cerrar sesión =====
        const logout = async () => {
            clearSession();
            try {
                await fetch(API_URL + '/logout', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                });
            } catch (e) {
                console.error('Logout:', e);
            }
            goLogin();
        };

        document.addEventListener('click', (e) => {
            if (e.target.closest('#logoutBtn')) {
                e.preventDefault();
                logout();
            }
        });

        // ===== Nombre e iniciales =====
        document.addEventListener('DOMContentLoaded', () => {
            try {
                const user = JSON.parse(localStorage.getItem('admin_user') || 'null');
                if (!user?.name) return;
                document.getElementById('userName').textContent = user.name;
                document.getElementById('userInitials').textContent =
                    user.name.split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase();
            } catch (e) {
                console.error('Datos de usuario:', e);
            }
        });
    })();
</script>
