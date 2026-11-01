@extends('layouts.admin')

@section('title', 'Actividades')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado de la Sección (mismo diseño que Destinos) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1">Gestión Turística</h2>
            <p class="text-muted small mb-0">Administra los destinos, actividades y alojamientos que los turistas pueden descubrir y reservar en la plataforma.</p>
        </div>
        <button type="button" id="newActivityBtn" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #F26419;">
            <i class="bi bi-plus-lg me-2"></i> Agregar Actividad
        </button>
    </div>

    <!-- Pestañas de Navegación -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link text-secondary bg-light px-4 fw-semibold" href="{{ route('admin.destinations.index') }}">Destinos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active text-white px-4 fw-semibold shadow-sm" style="background-color: #0D2A4D;" href="#">Actividades</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-secondary bg-light px-4 fw-semibold" href="#">Alojamiento</a>
        </li>
    </ul>

    <!-- Filtros -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-5 col-lg-4">
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="searchInput" class="form-control rounded-pill ps-5" placeholder="Buscar actividad...">
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <select id="destinationFilter" class="form-select rounded-pill">
                <option value="">Todos los destinos</option>
            </select>
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
                            <th class="py-3">Actividad</th>
                            <th class="py-3">Destino</th>
                            <th class="py-3">Precio</th>
                            <th class="py-3">Personas</th>
                            <th class="py-3">Duración</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="activitiesTableBody">
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Cargando actividades...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p id="showingCount" class="small text-muted mt-2 mb-0"></p>
</div>

    <!-- ========================================== -->
    <!-- MODAL: AGREGAR / EDITAR ACTIVIDAD          -->
    <!-- ========================================== -->
    <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 p-2">
                <div class="modal-header border-0 pb-0 pt-3 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="activityModalLabel">Agregar Actividad</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="activityForm" novalidate>
                    <input type="hidden" id="activity_id">
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label for="activity_name" class="form-label small fw-bold text-secondary">Nombre de la actividad *</label>
                            <input type="text" id="activity_name" name="activity_name" class="form-control" maxlength="150"
                                placeholder="Ej. Ascenso guiado al volcán" required>
                        </div>

                        <div class="mb-3">
                            <label for="destination_id" class="form-label small fw-bold text-secondary">Destino *</label>
                            <select id="destination_id" name="destination_id" class="form-select" required>
                                <option value="" disabled selected>Seleccionar...</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label for="price" class="form-label small fw-bold text-secondary">Precio ($) *</label>
                                <input type="number" id="price" name="price" class="form-control" min="0" step="0.01"
                                    placeholder="0.00" required>
                            </div>
                            <div class="col-4">
                                <label for="people" class="form-label small fw-bold text-secondary">Personas *</label>
                                <input type="number" id="people" name="people" class="form-control" min="1" step="1"
                                    value="1" required>
                            </div>
                            <div class="col-4">
                                <label for="duracion" class="form-label small fw-bold text-secondary">Duración *</label>
                                <input type="time" id="duracion" name="duracion" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label small fw-bold text-secondary">Notas</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3"
                                placeholder="Recomendaciones para el turista..."></textarea>
                        </div>

                        <!-- Interruptor de Estado (solo al editar, igual que en Destinos) -->
                        <div id="statusGroup" class="mb-1 d-none">
                            <label class="form-label small fw-bold text-secondary d-block">Estado *</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="status_switch" checked>
                                <label class="form-check-label fw-semibold text-dark" for="status_switch" id="statusLabel">Activo</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-3 px-4">
                        <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="saveActivityBtn" class="btn text-white fw-bold px-4" style="background-color: #F26419;">Guardar Actividad</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/activities.js')
    @endpush
@endsection