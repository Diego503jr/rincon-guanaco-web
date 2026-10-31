<form id="user-form">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Correo</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" id="password">
            <div class="form-text" id="password-hint"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Teléfono</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">País de origen</label>
            <input type="text" name="country_of_origin" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Rol</label>
            <select name="role" class="form-select" required>
                <option value="user">Usuario</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tipo de viajero</label>
            <select name="traveler_type" class="form-select">
                <option value="">Sin definir</option>
                <option value="mochilero">Mochilero</option>
                <option value="familia">Familia</option>
                <option value="pareja">Pareja</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Estado</label>
            <select name="status" class="form-select" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
