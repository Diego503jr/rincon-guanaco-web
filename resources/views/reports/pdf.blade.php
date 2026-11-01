<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 32px 40px 56px; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            color: #212529;
        }

        /* ===== Encabezado ===== */
        .header { width: 100%; border-bottom: 2px solid #f26419; padding-bottom: 8px; }
        .header td { vertical-align: middle; }
        .brand { font-size: 16px; font-weight: bold; color: #0d2a4d; }
        .brand span { color: #f26419; }
        .generated { text-align: right; font-size: 9px; color: #6c757d; }

        h1 { text-align: center; color: #0d2a4d; font-size: 22px; margin: 22px 0 2px; }
        .subtitle { text-align: center; color: #495057; margin: 0; }
        .period { text-align: center; color: #6c757d; font-size: 10px; margin: 4px 0 18px; }

        h2 { color: #0d2a4d; font-size: 13px; margin: 18px 0 6px; }

        /* ===== Tablas ===== */
        table.data { width: 100%; border-collapse: collapse; }
        table.data th {
            background: #0d2a4d;
            color: #fff;
            font-weight: bold;
            padding: 6px 8px;
            text-align: left;
        }
        table.data td { padding: 6px 8px; border-bottom: 1px solid #dee2e6; }
        table.data tr:nth-child(even) td { background: #f8f9fa; }
        table.data tfoot td { background: #e9ecef; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* ===== Resumen (tarjetas) ===== */
        table.summary { width: 100%; border-collapse: separate; border-spacing: 6px 0; }
        table.summary td {
            width: 25%;
            border: 1px solid #dee2e6;
            border-top: 3px solid #f26419;
            text-align: center;
            padding: 8px 4px;
        }
        .summary-label { font-size: 9px; color: #6c757d; }
        .summary-value { font-size: 16px; font-weight: bold; color: #0d2a4d; }

        .badge { padding: 2px 6px; border-radius: 8px; font-size: 9px; }
        .badge-success { background: #d1f0eb; color: #1f7a6f; }
        .badge-warning { background: #fde3d4; color: #b94a0f; }
        .star { color: #f26419; }
        .empty { text-align: center; color: #6c757d; padding: 14px; }

        .footer {
            position: fixed;
            bottom: -36px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="footer">Encuentra tu Rincón Guanaco · Panel administrativo · Reporte generado automáticamente</div>

    <!-- Encabezado -->
    <table class="header">
        <tr>
            <td style="width: 44px;">
                <img src="{{ public_path('images/logo.png') }}" height="38" alt="">
            </td>
            <td class="brand">Rincón <span>Guanaco</span></td>
            <td class="generated">
                Reporte generado<br>
                {{ $generatedAt->locale('es')->translatedFormat('d \d\e F \d\e Y, h:i A') }}
            </td>
        </tr>
    </table>

    <h1>{{ $title }}</h1>
    <p class="subtitle">Turismo en El Salvador</p>
    <p class="period">
        Periodo: {{ $from->locale('es')->translatedFormat('d \d\e F \d\e Y') }}
        al {{ $to->locale('es')->translatedFormat('d \d\e F \d\e Y') }}
    </p>

    <!-- Resumen general (común a todos los reportes) -->
    <h2>Resumen General</h2>
    <table class="summary">
        <tr>
            @foreach ($report['summary'] as $label => $value)
                <td>
                    <div class="summary-label">{{ $label }}</div>
                    <div class="summary-value">{{ $value }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    {{-- ===================== DESTINOS ===================== --}}
    @if ($type === 'destinos')
        <h2>Top 5 Destinos Más Visitados</h2>
        <table class="data">
            <thead>
                <tr>
                    <th class="text-center" style="width: 24px;">#</th>
                    <th>Destino</th>
                    <th>Departamento</th>
                    <th class="text-center">Veces agregado</th>
                    <th class="text-center">Calificación</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['top'] as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->department }}</td>
                        <td class="text-center">{{ $row->total }}</td>
                        <td class="text-center">
                            @if ($row->rating)
                                {{ number_format($row->rating, 1) }} <span class="star">★</span>
                            @else
                                Sin reseñas
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No hay destinos agregados a itinerarios en este periodo.</td></tr>
                @endforelse
            </tbody>
        </table>

        @php $totalUsers = array_sum($report['users']); @endphp
        <h2>Actividad de Usuarios (registrados en el periodo)</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['users'] as $label => $count)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="text-center">{{ $count }}</td>
                        <td class="text-center">{{ $totalUsers ? round($count / $totalUsers * 100) : 0 }}%</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="text-center">{{ $totalUsers }}</td>
                    <td class="text-center">{{ $totalUsers ? '100%' : '0%' }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ===================== ACTIVIDADES ===================== --}}
    @if ($type === 'actividades')
        <h2>Top 10 Actividades</h2>
        <table class="data">
            <thead>
                <tr>
                    <th class="text-center" style="width: 24px;">#</th>
                    <th>Actividad</th>
                    <th>Destino</th>
                    <th class="text-right">Precio</th>
                    <th class="text-center">Veces agregada</th>
                    <th class="text-right">Ingreso estimado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row->activity_name }}</td>
                        <td>{{ $row->destination }}</td>
                        <td class="text-right">${{ number_format($row->price, 2) }}</td>
                        <td class="text-center">{{ $row->total }}</td>
                        <td class="text-right">${{ number_format($row->price * $row->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No hay actividades registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- ===================== ITINERARIOS ===================== --}}
    @if ($type === 'itinerarios')
        <h2>Detalle de Itinerarios</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Itinerario</th>
                    <th>Usuario</th>
                    <th class="text-center">Actividades</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Creado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] as $itinerary)
                    <tr>
                        <td>{{ $itinerary->title }}</td>
                        <td>{{ $itinerary->user->name ?? 'Usuario eliminado' }}</td>
                        <td class="text-center">{{ $itinerary->activities_count }}</td>
                        <td class="text-right">${{ number_format($itinerary->total_price, 2) }}</td>
                        <td class="text-center">
                            @if ($itinerary->is_completed)
                                <span class="badge badge-success">Completado</span>
                            @else
                                <span class="badge badge-warning">En curso</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $itinerary->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No se crearon itinerarios en este periodo.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>

</html>