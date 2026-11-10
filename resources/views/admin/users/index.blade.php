@extends('layouts.admin')

@section('title', 'Usuarios')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- Encabezado de la Sección -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Gestión de Usuarios</h2>
            <p class="text-muted small mb-0">Administra las cuentas de administradores y turistas que usan la plataforma.</p>
        </div>
        <button type="button" id="btnNewUser" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #F26419;">
            <i class="bi bi-plus-lg me-2"></i> Agregar Nuevo Usuario
        </button>
    </div>

    <!-- Filtros -->
    <form id="filters" class="row g-3 mb-3">
        <div class="col-12 col-md-5">
            <div class="input-group shadow-sm rounded-3 overflow-hidden bg-white border">
                <span class="input-group-text bg-white border-0 text-muted ps-3">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-0 shadow-none ps-2" placeholder="Buscar por nombre o correo...">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="role" class="form-select shadow-sm border">
                <option value="">Todos los roles</option>
                <option value="admin">Admin</option>
                <option value="user">Usuario</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select shadow-sm border">
                <option value="">Todos los estados</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>
        <div class="col-12 col-md-1">
            <button type="reset" id="btn-reset" class="btn btn-light border w-100 shadow-sm">Limpiar</button>
        </div>
    </form>

    <!-- Tabla Principal -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3">Nombre</th>
                            <th class="py-3">Correo</th>
                            <th class="py-3">Teléfono</th>
                            <th class="py-3">Rol</th>
                            <th class="py-3">Viajero</th>
                            <th class="py-3">País</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="users-body">
                        <tr><td colspan="9" class="text-center text-muted py-4">Cargando…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <nav class="mt-3 d-flex justify-content-between align-items-center">
        <small class="text-muted" id="pagination-info"></small>
        <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
    </nav>
</div>

<!-- Botón oculto para abrir el modal vía data-bs (no depende de bootstrap global) -->
<button type="button" id="openUserModal" class="d-none" data-bs-toggle="modal" data-bs-target="#userModal"></button>

<!-- ========================================== -->
<!-- MODAL: CREAR / EDITAR USUARIO              -->
<!-- ========================================== -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 p-2">
            <div class="modal-header border-0 pb-0 pt-3 px-4">
                <h5 class="modal-title fw-bold text-dark" id="userModalLabel">Agregar Nuevo Usuario</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="userForm" novalidate>
                <input type="hidden" id="user_id" name="id">

                <div class="modal-body px-4 py-3">
                    <div id="formAlert" class="alert alert-danger small py-2 d-none"></div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Nombre completo *</label>
                            <input type="text" name="name" class="form-control" placeholder="Ej. María López" maxlength="100" required>
                            <div class="invalid-feedback" data-error-for="name"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Correo electrónico *</label>
                            <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" maxlength="150" required>
                            <div class="invalid-feedback" data-error-for="email"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">
                                Contraseña <span id="pwdRequiredMark">*</span>
                            </label>
                            <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                            <div class="invalid-feedback" data-error-for="password"></div>
                            <div class="form-text" id="pwdHelp" style="font-size: 0.75rem;"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repite la contraseña" autocomplete="new-password">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Teléfono</label>
                            <input type="text" name="phone" class="form-control" placeholder="Ej. 7000-0000" maxlength="50">
                            <div class="invalid-feedback" data-error-for="phone"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">País de origen</label>
                            <input type="text" name="country_of_origin" class="form-control" placeholder="Ej. El Salvador" maxlength="100">
                            <div class="invalid-feedback" data-error-for="country_of_origin"></div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary">Rol *</label>
                            <select name="role" class="form-select" required>
                                <option value="user">Usuario</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div class="invalid-feedback" data-error-for="role"></div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary">Tipo de viajero</label>
                            <select name="traveler_type" class="form-select">
                                <option value="">Sin especificar</option>
                                <option value="mochilero">Mochilero</option>
                                <option value="familia">Familia</option>
                                <option value="pareja">Pareja</option>
                            </select>
                            <div class="invalid-feedback" data-error-for="traveler_type"></div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary d-block">Estado *</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="status_switch" checked>
                                <label class="form-check-label fw-semibold text-dark" for="status_switch" id="status_label">Activo</label>
                            </div>
                            <input type="hidden" name="status" value="activo">
                            <div class="invalid-feedback d-block" data-error-for="status"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-3 px-4">
                    <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal" id="btnCancelUser">Cancelar</button>
                    <button type="submit" id="saveUserBtn" class="btn text-white fw-bold px-4" style="background-color: #F26419;">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const body        = document.getElementById('users-body');
    const filtersForm = document.getElementById('filters');
    const userForm    = document.getElementById('userForm');
    const formAlert   = document.getElementById('formAlert');
    const saveBtn     = document.getElementById('saveUserBtn');
    const statusSwitch = document.getElementById('status_switch');

    let currentRequest = null;
    let debounceTimer  = null;
    let currentPage    = 1;
    let usersCache     = {}; // id -> usuario (para llenar el modal sin otra petición)

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

    function notify(icon, title) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2500 });
        } else {
            alert(title);
        }
    }

    async function confirmAction(title, text) {
        if (typeof Swal !== 'undefined') {
            const r = await Swal.fire({
                title, text, icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#F26419',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar',
            });
            return r.isConfirmed;
        }
        return confirm(`${title}\n${text}`);
    }

    // ---------------- LISTADO ----------------

    async function loadUsers(page = 1) {
        currentPage = page;
        if (currentRequest) currentRequest.abort();
        currentRequest = new AbortController();

        const params = new URLSearchParams();
        for (const [key, value] of new FormData(filtersForm)) {
            if (value.trim()) params.set(key, value.trim());
        }
        params.set('page', page);

        body.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">Cargando…</td></tr>';

        try {
            const res = await fetch(`/api/admin/users?${params}`, {
                signal: currentRequest.signal,
                headers: authHeaders(),
            });

            if (res.status === 401) { window.location.href = '/login'; return; }
            if (!res.ok) throw new Error(`Error ${res.status}`);

            const json = await res.json();

            // Si la página quedó vacía (ej. al desactivar el último de la página), retrocede
            if (!json.data.length && json.current_page > 1) {
                return loadUsers(json.current_page - 1);
            }

            renderRows(json.data);
            renderPagination(json);
        } catch (err) {
            if (err.name === 'AbortError') return;
            body.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-4">No se pudo cargar la lista (${esc(err.message)}).</td></tr>`;
        }
    }

    function renderRows(users) {
        usersCache = {};
        users.forEach(u => usersCache[u.id] = u);

        if (!users.length) {
            body.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No se encontraron usuarios.</td></tr>';
            return;
        }

        body.innerHTML = users.map(u => `
            <tr>
                <td class="px-4 text-muted">${u.id}</td>
                <td class="fw-semibold text-dark">${esc(u.name)}</td>
                <td>${esc(u.email)}</td>
                <td>${esc(u.phone) || '—'}</td>
                <td><span class="badge rounded-pill ${u.role === 'admin' ? 'text-white' : 'bg-secondary-subtle text-secondary'}" ${u.role === 'admin' ? 'style="background-color:#0D2A4D;"' : ''}>${u.role === 'admin' ? 'Admin' : 'Usuario'}</span></td>
                <td class="text-capitalize">${esc(u.traveler_type) || '—'}</td>
                <td>${esc(u.country_of_origin) || '—'}</td>
                <td><span class="badge rounded-pill ${u.status === 'activo' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}">${u.status === 'activo' ? 'Activo' : 'Inactivo'}</span></td>
                <td class="text-end px-4 text-nowrap">
                    <button class="btn btn-sm btn-light border me-1" data-action="edit" data-id="${u.id}" title="Editar">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    ${u.status === 'activo'
                        ? `<button class="btn btn-sm btn-light border text-danger" data-action="deactivate" data-id="${u.id}" title="Desactivar"><i class="bi bi-person-x"></i></button>`
                        : `<button class="btn btn-sm btn-light border text-success" data-action="activate" data-id="${u.id}" title="Activar"><i class="bi bi-person-check"></i></button>`}
                </td>
            </tr>
        `).join('');
    }

    function renderPagination(p) {
        const ul   = document.getElementById('pagination');
        const info = document.getElementById('pagination-info');

        info.textContent = p.total ? `Mostrando ${p.from}–${p.to} de ${p.total} usuarios` : '';

        if (p.last_page <= 1) { ul.innerHTML = ''; return; }

        const item = (label, page, disabled = false, active = false) => `
            <li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">
                <a class="page-link ${active ? 'border-0' : ''}" href="#" data-page="${page}"
                   ${active ? 'style="background-color:#F26419;"' : ''}>${label}</a>
            </li>`;

        let html = item('&laquo;', p.current_page - 1, p.current_page === 1);
        const start = Math.max(1, p.current_page - 2);
        const end   = Math.min(p.last_page, p.current_page + 2);
        for (let i = start; i <= end; i++) html += item(i, i, false, i === p.current_page);
        html += item('&raquo;', p.current_page + 1, p.current_page === p.last_page);

        ul.innerHTML = html;
    }

    document.getElementById('pagination').addEventListener('click', e => {
        const a = e.target.closest('[data-page]');
        if (!a) return;
        e.preventDefault();
        if (a.parentElement.classList.contains('disabled')) return;
        loadUsers(Number(a.dataset.page));
    });

    // ---------------- MODAL ----------------

    function clearErrors() {
        formAlert.classList.add('d-none');
        formAlert.textContent = '';
        userForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        userForm.querySelectorAll('[data-error-for]').forEach(el => el.textContent = '');
    }

    function setStatus(value) {
        const active = value === 'activo';
        statusSwitch.checked = active;
        userForm.elements['status'].value = active ? 'activo' : 'inactivo';
        document.getElementById('status_label').textContent = active ? 'Activo' : 'Inactivo';
    }

    statusSwitch.addEventListener('change', () => setStatus(statusSwitch.checked ? 'activo' : 'inactivo'));

    function openModal(user = null) {
        userForm.reset();
        clearErrors();

        const isEdit = !!user;
        document.getElementById('user_id').value = user?.id ?? '';
        document.getElementById('userModalLabel').textContent = isEdit ? 'Editar Usuario' : 'Agregar Nuevo Usuario';
        saveBtn.textContent = isEdit ? 'Guardar Cambios' : 'Guardar Usuario';

        // En edición la contraseña es opcional
        document.getElementById('pwdRequiredMark').classList.toggle('d-none', isEdit);
        document.getElementById('pwdHelp').textContent = isEdit ? 'Déjala vacía para conservar la actual.' : '';

        if (isEdit) {
            ['name', 'email', 'phone', 'country_of_origin', 'role', 'traveler_type'].forEach(f => {
                userForm.elements[f].value = user[f] ?? '';
            });
            setStatus(user.status);
        } else {
            userForm.elements['role'].value = 'user';
            setStatus('activo');
        }

        document.getElementById('openUserModal').click();
    }

    document.getElementById('btnNewUser').addEventListener('click', () => openModal());

    // ---------------- GUARDAR ----------------

    userForm.addEventListener('submit', async e => {
        e.preventDefault();
        clearErrors();

        const id = document.getElementById('user_id').value;
        const isEdit = !!id;

        const data = Object.fromEntries(new FormData(userForm));
        delete data.id;
        // Campos opcionales vacíos -> null
        ['phone', 'country_of_origin', 'traveler_type'].forEach(f => { if (!data[f]) data[f] = null; });
        if (!data.password) { delete data.password; delete data.password_confirmation; }

        saveBtn.disabled = true;
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Guardando…';

        try {
            const res = await fetch(isEdit ? `/api/admin/users/${id}` : '/api/admin/users', {
                method: isEdit ? 'PUT' : 'POST',
                headers: authHeaders(),
                body: JSON.stringify(data),
            });

            if (res.status === 401) { window.location.href = '/login'; return; }

            const json = await res.json().catch(() => ({}));

            if (res.status === 422) {
                if (json.errors) {
                    Object.entries(json.errors).forEach(([field, msgs]) => {
                        const input = userForm.elements[field];
                        const box = userForm.querySelector(`[data-error-for="${field}"]`);
                        if (input) input.classList.add('is-invalid');
                        if (box) box.textContent = msgs[0];
                    });
                } else {
                    // Mensajes de negocio (ej. "No puedes cambiar tu propio rol o estado.")
                    formAlert.textContent = json.message || 'Datos inválidos.';
                    formAlert.classList.remove('d-none');
                }
                return;
            }

            if (!res.ok) throw new Error(json.message || `Error ${res.status}`);

            document.getElementById('btnCancelUser').click(); // cierra el modal
            notify('success', json.message || 'Guardado correctamente.');
            loadUsers(isEdit ? currentPage : 1);
        } catch (err) {
            formAlert.textContent = err.message;
            formAlert.classList.remove('d-none');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    });

    // ---------------- ACCIONES DE LA TABLA ----------------

    body.addEventListener('click', async e => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;

        const id = btn.dataset.id;
        const action = btn.dataset.action;

        if (action === 'edit') {
            openModal(usersCache[id]);
            return;
        }

        const deactivate = action === 'deactivate';

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
                    body: deactivate ? undefined : JSON.stringify({ status: 'activo' }),
                }
            );

            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.message || `Error ${res.status}`);

            notify('success', json.message || 'Estado actualizado.');
            loadUsers(currentPage);
        } catch (err) {
            notify('error', err.message);
        }
    });

    // ---------------- FILTROS ----------------

    filtersForm.elements['search'].addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => loadUsers(1), 400);
    });
    filtersForm.elements['role'].addEventListener('change', () => loadUsers(1));
    filtersForm.elements['status'].addEventListener('change', () => loadUsers(1));
    filtersForm.addEventListener('submit', e => { e.preventDefault(); loadUsers(1); });
    document.getElementById('btn-reset').addEventListener('click', () => setTimeout(() => loadUsers(1), 0));

    loadUsers();
</script>
@endpush