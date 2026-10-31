@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Usuarios</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Nuevo usuario</a>
    </div>

    <div id="alert-box"></div>

    <form id="filters" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o correo">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">Todos los roles</option>
                <option value="admin">Admin</option>
                <option value="user">Usuario</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Todos los estados</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>
        <div class="col-md-1">
            <button type="reset" class="btn btn-outline-secondary w-100" id="btn-reset">Limpiar</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Viajero</th>
                    <th>País</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="users-body">
                <tr>
                    <td colspan="9" class="text-center py-4">Cargando…</td>
                </tr>
            </tbody>
        </table>
    </div>

    <nav>
        <ul class="pagination" id="pagination"></ul>
    </nav>
@endsection

@push('scripts')
    <script>
        const body = document.getElementById('users-body');
        const filtersForm = document.getElementById('filters');
        let currentRequest = null; // para cancelar peticiones viejas
        let debounceTimer = null;

        const authHeaders = () => ({
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
        });

        // Evita inyección de HTML con datos de usuarios
        function esc(value) {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        }

        async function loadUsers() {
            // Cancela la petición anterior si el usuario sigue escribiendo
            if (currentRequest) currentRequest.abort();
            currentRequest = new AbortController();

            // Arma los parámetros con lo que haya en los filtros
            const params = new URLSearchParams();
            for (const [key, value] of new FormData(filtersForm)) {
                if (value.trim()) params.set(key, value.trim());
            }

            body.innerHTML = '<tr><td colspan="9" class="text-center py-4">Cargando…</td></tr>';

            try {
                const res = await fetch(`/api/admin/users?${params}`, {
                    signal: currentRequest.signal,
                    headers: authHeaders(),
                });

                if (res.status === 401) {
                    window.location.href = '/login'; // token vencido o inexistente
                    return;
                }
                if (!res.ok) throw new Error(`Error ${res.status}`);

                const json = await res.json();
                renderRows(json.data);

            } catch (err) {
                if (err.name === 'AbortError') return; // cancelada a propósito, no es un error
                body.innerHTML =
                    `<tr><td colspan="9" class="text-center text-danger py-4">No se pudo cargar la lista (${esc(err.message)}).</td></tr>`;
            }
        }

        function renderRows(users) {
            if (!users.length) {
                body.innerHTML =
                    '<tr><td colspan="9" class="text-center text-muted py-4">No se encontraron usuarios.</td></tr>';
                return;
            }

            body.innerHTML = users.map(u => `
                <tr>
                    <td>${u.id}</td>
                    <td>${esc(u.name)}</td>
                    <td>${esc(u.email)}</td>
                    <td>${esc(u.phone) || '—'}</td>
                    <td><span class="badge ${u.role === 'admin' ? 'bg-primary' : 'bg-secondary'}">${esc(u.role)}</span></td>
                    <td>${esc(u.traveler_type) || '—'}</td>
                    <td>${esc(u.country_of_origin) || '—'}</td>
                    <td><span class="badge ${u.status === 'activo' ? 'bg-success' : 'bg-danger'}">${esc(u.status)}</span></td>
                    <td class="text-end">
                        <a href="/admin/users/${u.id}/editar" class="btn btn-sm btn-outline-primary">Editar</a>
                        ${u.status === 'activo'
                            ? `<button class="btn btn-sm btn-outline-danger" data-action="deactivate" data-id="${u.id}">Desactivar</button>`
                            : `<button class="btn btn-sm btn-outline-success" data-action="activate" data-id="${u.id}">Activar</button>`}
                    </td>
                </tr>
            `).join('');
        }

        async function confirmAction(title, text) {
            if (typeof Swal !== 'undefined') {
                const r = await Swal.fire({
                    title,
                    text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F26419',
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar',
                });
                return r.isConfirmed;
            }
            return confirm(`${title}\n${text}`);
        }

        // Botones Desactivar / Activar
        body.addEventListener('click', async e => {
            const btn = e.target.closest('[data-action]');
            if (!btn) return;

            const id = btn.dataset.id;
            const deactivate = btn.dataset.action === 'deactivate';

            if (deactivate) {
                const ok = await confirmAction(
                    '¿Desactivar usuario?',
                    'No podrá iniciar sesión hasta que lo vuelvas a activar.'
                );
                if (!ok) return;
            }

            try {
                const res = await fetch(
                    deactivate ? `/api/admin/users/${id}` : `/api/admin/users/${id}/status`, {
                        method: deactivate ? 'DELETE' : 'PATCH',
                        headers: authHeaders(),
                        body: deactivate ? undefined : JSON.stringify({
                            status: 'activo'
                        }),
                    }
                );

                const json = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(json.message || `Error ${res.status}`);

                loadUsers(); // refresca el grid respetando los filtros actuales
            } catch (err) {
                alert(err.message);
            }
        });

        // ---- Filtros dinámicos ----

        // Texto: espera 400 ms después de la última tecla
        filtersForm.elements['search'].addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(loadUsers, 400);
        });

        // Selects: recargan al instante
        filtersForm.elements['role'].addEventListener('change', loadUsers);
        filtersForm.elements['status'].addEventListener('change', loadUsers);

        // Enter no debe recargar la página
        filtersForm.addEventListener('submit', e => {
            e.preventDefault();
            loadUsers();
        });

        // Limpiar: el reset vacía los campos y luego recargamos
        document.getElementById('btn-reset').addEventListener('click', () => {
            setTimeout(loadUsers, 0);
        });

        loadUsers();

        loadUsers();
    </script>
@endpush
