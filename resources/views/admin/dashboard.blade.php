@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="mb-1">Dashboard</h1>
    <p class="text-muted">Resumen general de la plataforma</p>

    <!-- Tarjetas de contadores (se llenan desde dashboard.js) -->
    <div class="row g-3 mb-4">
        @foreach ([
            ['key' => 'users', 'label' => 'Total usuarios', 'icon' => 'bi-people-fill', 'color' => 'text-primary'],
            ['key' => 'active_itineraries', 'label' => 'Itinerarios activos', 'icon' => 'bi-map-fill', 'color' => 'text-success'],
            ['key' => 'destinations', 'label' => 'Destinos registrados', 'icon' => 'bi-pin-map-fill', 'color' => 'text-primary'],
            ['key' => 'forum_alerts', 'label' => 'Alertas de Foro', 'icon' => 'bi-chat-square-text-fill', 'color' => 'text-accent'],
        ] as $card)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border shadow-sm bg-white rounded-3 h-100">
                    <div class="card-body">
                        <i class="bi {{ $card['icon'] }} {{ $card['color'] }} fs-3"></i>
                        <div class="text-muted mt-2">{{ $card['label'] }}</div>
                        <div id="count-{{ $card['key'] }}" class="h3 fw-bold text-primary mb-2">--</div>
                        <div id="trend-{{ $card['key'] }}" class="small text-muted">&nbsp;</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Gráficos -->
    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="card border shadow-sm bg-white rounded-3 h-100">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-bar-chart-fill text-primary me-2"></i>Destinos más visitados
                        </h5>
                        <select id="daysFilter" class="form-select form-select-sm rounded-pill w-auto">
                            <option value="7">Últimos 7 días</option>
                            <option value="30" selected>Últimos 30 días</option>
                            <option value="90">Últimos 90 días</option>
                            <option value="365">Último año</option>
                        </select>
                    </div>
                    <p class="text-muted small mb-3">Veces que el destino se agregó a un itinerario</p>
                    <div style="height: 300px;">
                        <canvas id="destinationsChart"></canvas>
                        <div id="noDestinationsMsg" class="h-100 d-none align-items-center justify-content-center text-muted">
                            No hay destinos agregados a itinerarios en este periodo.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card border shadow-sm bg-white rounded-3 h-100">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-luggage-fill text-primary me-2"></i>Tipos de viajero
                    </h5>
                    <div class="position-relative mx-auto" style="height: 230px; max-width: 230px;">
                        <canvas id="travelersChart"></canvas>
                        <!-- Total en el centro de la dona -->
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <div class="small text-muted">Total</div>
                            <div id="travelersTotal" class="h4 fw-bold text-primary mb-0">--</div>
                        </div>
                    </div>
                    <ul id="travelersLegend" class="list-unstyled mt-4 mb-0"></ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        @vite('resources/js/dashboard.js')
    @endpush
@endsection