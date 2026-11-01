<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\Review;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Tipos de reporte disponibles: clave => título del PDF
    private const TYPES = [
        'destinos'    => 'Destinos populares',
        'actividades' => 'Actividades más agregadas',
        'itinerarios' => 'Itinerarios creados',
    ];

    public function pdf(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:' . implode(',', array_keys(self::TYPES))],
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to   = Carbon::parse($data['to'])->endOfDay();

        $report = match ($data['type']) {
            'destinos'    => $this->destinationsReport($from, $to),
            'actividades' => $this->activitiesReport($from, $to),
            'itinerarios' => $this->itinerariesReport($from, $to),
        };

        $pdf = Pdf::loadView('reports.pdf', [
            'type'        => $data['type'],
            'title'       => self::TYPES[$data['type']],
            'from'        => $from,
            'to'          => $to,
            'generatedAt' => now('America/El_Salvador'), // la app usa UTC; se muestra la hora local
            'report'      => $report,
        ])
            ->setPaper('letter')
            ->setOption('isFontSubsettingEnabled', true); // solo incrusta las letras usadas (PDF más liviano)

        $fileName = "reporte-{$data['type']}-{$from->format('Ymd')}-{$to->format('Ymd')}.pdf";

        // stream = se muestra en el navegador (vista previa); el JS se encarga de la descarga
        return $pdf->stream($fileName);
    }

    // ===== Reporte: Destinos populares =====
    private function destinationsReport(Carbon $from, Carbon $to): array
    {
        // Veces que cada destino se agregó a itinerarios (vía sus actividades) en el periodo
        $uses = DB::table('itineraries_activities')
            ->join('activities', 'activities.id', '=', 'itineraries_activities.activity_id')
            ->whereBetween('itineraries_activities.created_at', [$from, $to])
            ->select('activities.destination_id', DB::raw('COUNT(*) as total'))
            ->groupBy('activities.destination_id');

        $ratings = DB::table('reviews')
            ->select('destination_id', DB::raw('AVG(rating) as rating'))
            ->groupBy('destination_id');

        $top = DB::table('destinations')
            ->joinSub($uses, 'uses', 'uses.destination_id', '=', 'destinations.id')
            ->leftJoinSub($ratings, 'ratings', 'ratings.destination_id', '=', 'destinations.id')
            ->select('destinations.name', 'destinations.department', 'uses.total', 'ratings.rating')
            ->orderByDesc('uses.total')
            ->limit(5)
            ->get();

        // Turistas registrados en el periodo: nacionales vs. extranjeros
        $newUsers = User::where('role', 'user')->whereBetween('created_at', [$from, $to]);

        $users = [
            'Turistas nacionales'  => (clone $newUsers)->where('country_of_origin', 'El Salvador')->count(),
            'Turistas extranjeros' => (clone $newUsers)->where('country_of_origin', '!=', 'El Salvador')->count(),
        ];

        $unspecified = (clone $newUsers)->whereNull('country_of_origin')->count();
        if ($unspecified > 0) {
            $users['Sin país registrado'] = $unspecified;
        }

        return [
            'summary' => [
                'Total destinos'   => DB::table('destinations')->where('status', 'activo')->count(),
                'Veces agregados'  => DB::table('itineraries_activities')->whereBetween('created_at', [$from, $to])->count(),
                'Usuarios'         => User::where('role', 'user')->count(),
                'Calificación'     => number_format((float) Review::avg('rating'), 1) . '/5',
            ],
            'top'     => $top,
            'users'   => $users,
        ];
    }

    // ===== Reporte: Actividades más agregadas =====
    private function activitiesReport(Carbon $from, Carbon $to): array
    {
        $uses = DB::table('itineraries_activities')
            ->whereBetween('created_at', [$from, $to])
            ->select('activity_id', DB::raw('COUNT(*) as total'))
            ->groupBy('activity_id');

        $rows = DB::table('activities')
            ->join('destinations', 'destinations.id', '=', 'activities.destination_id')
            ->leftJoinSub($uses, 'uses', 'uses.activity_id', '=', 'activities.id')
            ->select(
                'activities.activity_name',
                'destinations.name as destination',
                'activities.price',
                DB::raw('COALESCE(uses.total, 0) as total')
            )
            ->orderByDesc('total')
            ->orderBy('activities.activity_name')
            ->limit(10)
            ->get();

        return [
            'summary' => [
                'Total actividades' => DB::table('activities')->count(),
                'Veces agregadas'   => DB::table('itineraries_activities')->whereBetween('created_at', [$from, $to])->count(),
                'Precio promedio'   => '$' . number_format((float) DB::table('activities')->avg('price'), 2),
                'Ingreso estimado'  => '$' . number_format($rows->sum(fn($row) => $row->price * $row->total), 2),
            ],
            'rows' => $rows,
        ];
    }

    // ===== Reporte: Itinerarios creados =====
    private function itinerariesReport(Carbon $from, Carbon $to): array
    {
        $itineraries = Itinerary::with('user:id,name')
            ->withCount('activities')
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        $completed = $itineraries->where('is_completed', true)->count();

        return [
            'summary' => [
                'Itinerarios'    => $itineraries->count(),
                'Completados'    => $completed,
                'En curso'       => $itineraries->count() - $completed,
                'Monto promedio' => '$' . number_format((float) $itineraries->avg('total_price'), 2),
            ],
            'rows' => $itineraries,
        ];
    }
}