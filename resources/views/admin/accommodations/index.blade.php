@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Gestión Turística</h2>
            <p class="text-muted small mb-0">Administra los destinos, actividades y alojamientos que los turistas pueden descubrir en la plataforma.</p>
        </div>
        <button type="button" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #F26419;" data-bs-toggle="modal" data-bs-target="#createAccommodationModal">
            <i class="bi bi-plus-lg me-2"></i> Agregar Nuevo Alojamiento
        </button>
    </div>

    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link text-secondary bg-light px-4 fw-semibold" href="{{ route('admin.destinations.index') }}">Destinos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-secondary bg-light px-4 fw-semibold" href="{{ route('admin.destinations.activities') }}">Actividades</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active text-white px-4 fw-semibold shadow-sm" style="background-color: #0D2A4D;" href="#">Alojamientos</a>
        </li>
    </ul>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-5">
            <div class="input-group shadow-sm rounded-3 overflow-hidden bg-white border">
                <span class="input-group-text bg-white border-0 text-muted ps-3">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="searchAccommodationInput" class="form-control border-0 shadow-none ps-2" placeholder="Buscar alojamiento...">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-3">ID</th>
                            <th class="py-3">Fotografía</th>
                            <th class="py-3">Alojamiento y Contacto</th>
                            <th class="py-3">Ubicación / Dirección</th>
                            <th class="py-3">Tipo</th>
                            <th class="py-3">Precio / Horarios</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3 text-end px-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="accommodationsTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: CREAR -->
<div class="modal fade" id="createAccommodationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 p-2">
            <div class="modal-header border-0 pb-0 pt-3 px-4">
                <h5 class="modal-title fw-bold text-dark">Agregar Nuevo Alojamiento</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="createAccommodationForm" enctype="multipart/form-data">
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-primary">Fotografía del alojamiento *</label>
                                <div class="border border-2 border-dashed rounded-4 p-4 text-center bg-light position-relative" style="cursor: pointer; min-height: 180px; display: flex; flex-direction: column; justify-content: center; align-items: center;" onclick="document.getElementById('acc_image_url').click()">
                                    <i class="bi bi-image fs-1 text-muted mb-2"></i>
                                    <span class="small text-muted d-block fw-semibold mb-1">Haz clic para agregar una imágen</span>
                                    <input type="file" id="acc_image_url" name="image_url" class="d-none" accept="image/*">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-primary">Destino asociado *</label>
                                <select id="create_acc_destination_id" name="destination_id" class="form-select" required>
                                    <option value="" disabled selected>Busca y selecciona un destino...</option>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-primary">Número de reservas</label>
                                    <input type="number" name="reserva" class="form-control" placeholder="Ej. 3">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-primary">Precio por noche *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">$</span>
                                        <input type="number" step="0.01" name="price" class="form-control border-start-0" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-primary">Nombre del alojamiento *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-primary">Dirección</label>
                                <input type="text" name="address" class="form-control">
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-primary">Teléfono</label>
                                    <input type="text" name="contact_phone" class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-primary">Correo</label>
                                    <input type="email" name="email" class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-primary">Tipo de alojamiento *</label>
                                <select name="type" class="form-select" required>
                                    <option value="" disabled selected>Seleccionar...</option>
                                    <option value="hotel">Hotel</option>
                                    <option value="apartamento">Apartamento</option>
                                    <option value="hostal">Hostal</option>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-primary">Hora de entrada *</label>
                                    <input type="time" name="check_in" class="form-control" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-primary">Hora de salida *</label>
                                    <input type="time" name="check_out" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-3 px-4">
                    <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white fw-bold px-4" style="background-color: #F26419;">Guardar Alojamiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: EDITAR -->
<div class="modal fade" id="editAccommodationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 p-2">
            <div class="modal-header border-0 pb-0 pt-3 px-4">
                <h5 class="modal-title fw-bold text-dark">Editar Alojamiento</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="editAccommodationForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_acc_id" name="id">
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Fotografía del alojamiento *</label>
                                <div class="border border-2 border-dashed rounded-4 p-4 text-center bg-light position-relative" style="cursor: pointer; min-height: 180px; display: flex; flex-direction: column; justify-content: center; align-items: center;" onclick="document.getElementById('edit_acc_image_url').click()">
                                    <i class="bi bi-image fs-1 text-muted mb-2"></i>
                                    <span class="small text-muted d-block fw-semibold">Haz clic para cambiar la imagen</span>
                                    <input type="file" id="edit_acc_image_url" name="image_url" class="d-none" accept="image/*">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Destino asociado *</label>
                                <select id="edit_acc_destination_id" name="destination_id" class="form-select" required>
                                    <option value="" disabled selected>Busca y selecciona un destino...</option>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Número de reservas</label>
                                    <input type="number" id="edit_acc_reserva" name="reserva" class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Precio por noche *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">$</span>
                                        <input type="number" step="0.01" id="edit_acc_price" name="price" class="form-control border-start-0" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Nombre del alojamiento *</label>
                                <input type="text" id="edit_acc_name" name="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Dirección</label>
                                <input type="text" id="edit_acc_address" name="address" class="form-control">
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Teléfono</label>
                                    <input type="text" id="edit_acc_contact_phone" name="contact_phone" class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Correo</label>
                                    <input type="email" id="edit_acc_email" name="email" class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Tipo de alojamiento *</label>
                                <select id="edit_acc_type" name="type" class="form-select" required>
                                    <option value="hotel">Hotel</option>
                                    <option value="apartamento">Apartamento</option>
                                    <option value="hostal">Hostal</option>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Hora de entrada *</label>
                                    <input type="time" id="edit_acc_check_in" name="check_in" class="form-control" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Hora de salida *</label>
                                    <input type="time" id="edit_acc_check_out" name="check_out" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold text-secondary d-block">Estado *</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="edit_acc_status_switch" checked onchange="document.getElementById('edit_acc_status').value = this.checked ? 'activo' : 'inactivo'">
                                    <label class="form-check-label fw-semibold text-dark" for="edit_acc_status_switch">Activo</label>
                                </div>
                                <input type="hidden" id="edit_acc_status" name="status" value="activo">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-3 px-4">
                    <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white fw-bold px-4" style="background-color: #F26419;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@vite(['resources/js/accommodations.js'])