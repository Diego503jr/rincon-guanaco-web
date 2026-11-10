@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado de la Sección -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Gestión Turística</h2>
            <p class="text-muted small mb-0">Administra los destinos, actividades y alojamientos que los turistas pueden descubrir y reservar en la plataforma.</p>
        </div>
        <button type="button" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #F26419;" data-bs-toggle="modal" data-bs-target="#createDestinationModal">
            <i class="bi bi-plus-lg me-2"></i> Agregar Nuevo Destino
        </button>
    </div>

    <!-- Pestañas de Navegación -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link active text-white px-4 fw-semibold shadow-sm" style="background-color: #0D2A4D;" href="#">Destinos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-secondary bg-light px-4 fw-semibold" href="{{ route('admin.destinations.activities') }}">Actividades</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-secondary bg-light px-4 fw-semibold" href="{{ route('admin.destinations.accommodations') }}">Alojamientos</a>
        </li>
    </ul>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-5">
            <div class="input-group shadow-sm rounded-3 overflow-hidden bg-white border">
                <span class="input-group-text bg-white border-0 text-muted ps-3">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="searchDestinationInput" class="form-control border-0 shadow-none ps-2" placeholder="Buscar destino...">
            </div>
        </div>
    </div>

    <!-- Tabla Principal -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3">Fotografía</th>
                            <th class="py-3">Nombre</th>
                            <th class="py-3">Ubicación</th>
                            <th class="py-3">Categoría</th>
                            <th>Tipo de Viajero</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="destinationsTableBody">
                        <!-- Creado dinámicamente desde destinations.js -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: AGREGAR NUEVO DESTINO              -->
<!-- ========================================== -->
<div class="modal fade" id="createDestinationModal" tabindex="-1" aria-labelledby="createDestinationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 p-2">
            <div class="modal-header border-0 pb-0 pt-3 px-4">
                <h5 class="modal-title fw-bold text-dark" id="createDestinationModalLabel">Agregar Nuevo Destino</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="createDestinationForm" enctype="multipart/form-data">
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <!-- Columna Izquierda -->
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-primary">Fotografía del destino *</label>
                                <div class="border border-2 border-dashed rounded-4 p-4 text-center bg-light position-relative" style="cursor: pointer; min-height: 180px; display: flex; flex-direction: column; justify-content: center; align-items: center;" onclick="document.getElementById('image_url').click()">
                                    <i class="bi bi-image fs-1 text-muted mb-2"></i>
                                    <span class="small text-muted d-block fw-semibold mb-1">Haz clic para agregar una imágen</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">Formatos: JPG, PNG Máx. 5MB</span>
                                    <input type="file" id="image_url" name="image_url" class="d-none" accept="image/*" required>
                                </div>
                                <div id="imagePreviewName" class="small text-success mt-1 text-center text-truncate"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-primary">Nombre del destino *</label>
                                <input type="text" name="name" class="form-control" placeholder="Ej. El Tunco" required>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-primary">Departamento *</label>
                                    <select id="department" name="department" class="form-select" required>
                                        <option value="" disabled selected>Seleccionar...</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-primary">Municipio *</label>
                                    <select id="municipality" name="municipality" class="form-select" required>
                                        <option value="" disabled selected>Seleccionar...</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-primary">Descripción *</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Describe brevemente el destino..." required></textarea>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-primary">Categoría *</label>
                                    <select name="category" class="form-select" required>
                                        <option value="" disabled selected>Seleccionar...</option>
                                        <option value="playa">Playa</option>
                                        <option value="montaña">Montaña</option>
                                        <option value="cultural">Cultural</option>
                                        <option value="gastronomico">Gastronómico</option>
                                        <option value="aventura">Aventura</option>
                                        <option value="relax">Relax</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-primary">Tipo de viajero</label>
                                    <select name="traveler_type" class="form-select">
                                        <option value="" disabled selected>Seleccionar...</option>
                                        <option value="mochilero">Mochilero</option>
                                        <option value="familia">Familia</option>
                                        <option value="pareja">Pareja</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha (Mapa) -->
                        <div class="col-12 col-md-6 d-flex flex-column">
                            <label class="form-label small fw-bold text-primary">Ubicación *</label>
                            <div id="createMap" class="rounded-4 border shadow-sm flex-grow-1 mb-2" style="min-height: 280px;"></div>

                            <div class="row g-2">
                                <div class="col-6"><input type="number" step="any" id="latitude" name="latitude" class="form-control form-control-sm" placeholder="Latitud" readonly required></div>
                                <div class="col-6"><input type="number" step="any" id="longitude" name="longitude" class="form-control form-control-sm" placeholder="Longitud" readonly required></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-3 px-4">
                    <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="saveDestinationBtn" class="btn text-white fw-bold px-4" style="background-color: #F26419;">Guardar Destino</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: EDITAR DESTINO                      -->
<!-- ========================================== -->
<div class="modal fade" id="editDestinationModal" tabindex="-1" aria-labelledby="editDestinationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 p-2">
            <div class="modal-header border-0 pb-0 pt-3 px-4">
                <h5 class="modal-title fw-bold text-dark" id="editDestinationModalLabel">Editar Destino</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="editDestinationForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_destination_id" name="id">
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <!-- Columna Izquierda -->
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Fotografía del destino *</label>
                                <div class="border border-2 border-dashed rounded-4 p-4 text-center bg-light position-relative" style="cursor: pointer; min-height: 180px; display: flex; flex-direction: column; justify-content: center; align-items: center;" onclick="document.getElementById('edit_image_url').click()">
                                    <i class="bi bi-image fs-1 text-muted mb-2"></i>
                                    <span class="small text-muted d-block fw-semibold">Haz clic para cambiar la imagen</span>
                                    <input type="file" id="edit_image_url" name="image_url" class="d-none" accept="image/*">
                                </div>
                                <div id="editImagePreviewName" class="small text-success mt-1 text-center text-truncate"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Nombre del destino *</label>
                                <input type="text" id="edit_name" name="name" class="form-control" required>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Departamento *</label>
                                    <select id="edit_department" name="department" class="form-select" required>
                                        <option value="" disabled selected>Seleccionar...</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Municipio *</label>
                                    <select id="edit_municipality" name="municipality" class="form-select" required>
                                        <option value="" disabled selected>Seleccionar...</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Descripción *</label>
                                <textarea id="edit_description" name="description" class="form-control" rows="3" required></textarea>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Categoría *</label>
                                    <select id="edit_category" name="category" class="form-select" required>
                                        <option value="playa">Playa</option>
                                        <option value="montaña">Montaña</option>
                                        <option value="cultural">Cultural</option>
                                        <option value="gastronomico">Gastronómico</option>
                                        <option value="aventura">Aventura</option>
                                        <option value="relax">Relax</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Tipo de viajero</label>
                                    <select id="edit_traveler_type" name="traveler_type" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="mochilero">Mochilero</option>
                                        <option value="familia">Familia</option>
                                        <option value="pareja">Pareja</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Interruptor de Estado (Switch) -->
                            <div class="mb-2">
                                <label class="form-label small fw-bold text-secondary d-block">Estado *</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="edit_status_switch" checked onchange="document.getElementById('edit_status').value = this.checked ? 'activo' : 'inactivo'">
                                    <label class="form-check-label fw-semibold text-dark" for="edit_status_switch">Activo</label>
                                </div>
                                <input type="hidden" id="edit_status" name="status" value="activo">
                            </div>
                        </div>

                        <!-- Columna Derecha (Mapa) -->
                        <div class="col-12 col-md-6 d-flex flex-column">
                            <label class="form-label small fw-bold text-secondary">Ubicación *</label>
                            <div id="editMap" class="rounded-4 border shadow-sm flex-grow-1 mb-2" style="min-height: 280px;"></div>

                            <div class="row g-2">
                                <div class="col-6"><input type="number" step="any" id="edit_latitude" name="latitude" class="form-control form-control-sm" placeholder="Latitud" readonly required></div>
                                <div class="col-6"><input type="number" step="any" id="edit_longitude" name="longitude" class="form-control form-control-sm" placeholder="Longitud" readonly required></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-3 px-4">
                    <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="updateDestinationBtn" class="btn text-white fw-bold px-4" style="background-color: #F26419;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@vite(['resources/js/destinations.js'])