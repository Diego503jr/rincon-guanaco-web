@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- Cabecera y Buscador -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
    <div>
        <h3 class="mb-1 fw-bold">Moderación de Foro - Reseñas</h3>
        <p class="text-body-color mb-0 small">Revisa y gestiona los comentarios reportados por la comunidad.</p>
    </div>
    <div class="position-relative" style="width: 100%; max-width: 300px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary" viewBox="0 0 16 16">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
        </svg>
        <!-- "searchInput" para busquedas -->
        <input type="text" id="searchInput" class="form-control form-control-sm rounded-pill ps-5 py-2 border-secondary" placeholder="Buscar reporte...">
    </div>
</div>

<!-- Contenedor principal -->
<div class="col-12 col-xl-9 mx-auto">

    <!-- Contenedor de Tarjetas de reportes -->
    <div class="d-flex flex-column gap-3 mb-4" id="reportsContainer">
        <!-- Estado de carga inicial -->
        <div id="loadingMsg" class="text-center text-muted py-4">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            Cargando reportes...
        </div>
    </div>

    <!-- Mensaje de "No hay resultados" -->
    <div id="noResultsMsg" class="text-center text-muted py-3" style="display: none;">
        No se encontraron reportes que coincidan con la búsqueda.
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center text-body-color">
        <span id="showingCount" class="small">Mostrando 0 comentarios</span>
        <div class="d-flex align-items-center gap-2">
            <button id="prevBtn" class="btn btn-sm btn-light border p-1 d-flex align-items-center justify-content-center text-muted rounded-circle" style="width: 32px; height: 32px;" disabled>
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
                </svg>
            </button>
            <button id="pageIndicator" class="btn btn-sm text-white p-1 d-flex align-items-center justify-content-center rounded" style="width: 32px; height: 32px; background-color: #3b82f6; border-color: #3b82f6;">
                1
            </button>
            <button id="nextBtn" class="btn btn-sm btn-light border p-1 d-flex align-items-center justify-content-center text-muted rounded-circle" style="width: 32px; height: 32px;">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z" />
                </svg>
            </button>
        </div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/moderacion.js')
@endpush
@endsection
