@extends('layouts.admin')

@section('title', 'Reportes')

@section('content')
    <h1 class="mb-1">Reportes y Descargar PDF</h1>
    <p class="text-muted">Genera y descarga reportes de la plataforma en formato PDF.</p>

    <!-- Filtros -->
    <div class="card border shadow-sm bg-white rounded-3 mb-3">
        <div class="card-body">
            <form id="reportForm" class="row g-3 align-items-end" novalidate>
                <div class="col-12 col-sm-6 col-xl">
                    <label for="from" class="form-label small fw-bold text-muted">Desde</label>
                    <input type="date" id="from" class="form-control" required>
                </div>
                <div class="col-12 col-sm-6 col-xl">
                    <label for="to" class="form-label small fw-bold text-muted">Hasta</label>
                    <input type="date" id="to" class="form-control" required>
                </div>
                <div class="col-12 col-xl">
                    <label for="type" class="form-label small fw-bold text-muted">Tipo de reporte</label>
                    <select id="type" class="form-select" required>
                        <option value="destinos">Destinos populares</option>
                        <option value="actividades">Actividades más agregadas</option>
                        <option value="itinerarios">Itinerarios creados</option>
                    </select>
                </div>
                <div class="col-12 col-xl-auto d-flex gap-2">
                    <button type="button" id="previewBtn" class="btn btn-outline-primary flex-shrink-0" title="Actualizar vista previa">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button type="submit" id="downloadBtn" class="btn btn-accent text-white fw-semibold flex-grow-1 text-nowrap">
                        <i class="bi bi-download me-2"></i>Generar y Descargar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Vista previa del PDF -->
    <div class="card border-0 rounded-3 bg-secondary bg-opacity-50 p-2 p-lg-3">
        <div id="previewLoading" class="text-center text-muted py-5">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            Generando vista previa...
        </div>
        <iframe id="previewFrame" class="w-100 rounded-2 bg-white d-none" style="height: 75vh;" title="Vista previa del reporte"></iframe>
    </div>

    @push('scripts')
        @vite('resources/js/reports.js')
    @endpush
@endsection