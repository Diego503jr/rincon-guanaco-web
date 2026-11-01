<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\ReportReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $request->validate([
            'days' => ['nullable', 'integer', 'in:7,30,90,365'],
        ]);

        $users        = User::where('role', 'user');
        $itineraries  = Itinerary::where('is_completed', false);
        // Se consulta con DB::table para no depender de la lógica del modelo Destination
        $destinations = DB::table('destinations')->where('status', 'activo');
        $alerts       = ReportReview::where('status', 'activo');

        // ===== Tarjetas de contadores =====
        $counters = [
            'users'              => $this->counter($users),
            'active_itineraries' => $this->counter($itineraries),
            'destinations'       => $this->counter($destinations),
            'forum_alerts'       => $this->counter($alerts),
        ];

        return response()->json([
            'counters'         => $counters,
            'top_destinations' => $this->topDestinations($request->integer('days', 30)),
            'traveler_types'   => $this->travelerTypes(),
        ]);
    }

        // Total + crecimiento: cuánto creció el total este mes respecto al total del mes anterior
    private function counter($query): array
    {
        $total  = (clone $query)->count();
        $before = (clone $query)->where('created_at', '<', now()->startOfMonth())->count();
        $new    = $total - $before;

        return [
            'total'      => $total,
            'this_month' => $new,
            // null cuando antes de este mes no había registros (no se puede calcular %)
            'trend'      => $before > 0 ? round($new / $before * 100) : null,
        ];
    }

    // Destinos que más se agregan a itinerarios (vía sus actividades) en los últimos X días
    private function topDestinations(int $days): array
    {
        $rows = DB::table('itineraries_activities')
            ->join('activities', 'activities.id', '=', 'itineraries_activities.activity_id')
            ->join('destinations', 'destinations.id', '=', 'activities.destination_id')
            ->where('itineraries_activities.created_at', '>=', now()->subDays($days))
            ->select('destinations.name', DB::raw('COUNT(*) as total'))
            ->groupBy('destinations.id', 'destinations.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'labels' => $rows->pluck('name'),
            'data'   => $rows->pluck('total')->map(fn($total) => (int) $total),
        ];
    }

    // Usuarios agrupados por tipo de viajero
    private function travelerTypes(): array
    {
        $names = [
            'mochilero' => 'Mochileros',
            'familia'   => 'Familiares',
            'pareja'    => 'Parejas',
        ];

        $counts = User::where('role', 'user')
            ->select('traveler_type', DB::raw('COUNT(*) as total'))
            ->groupBy('traveler_type')
            ->pluck('total', 'traveler_type');

        $labels = [];
        $data = [];

        foreach ($names as $key => $label) {
            $labels[] = $label;
            $data[] = (int) ($counts[$key] ?? 0);
        }

        // Usuarios que no eligieron tipo de viajero
        $unspecified = (int) ($counts[''] ?? 0);
        if ($unspecified > 0) {
            $labels[] = 'Sin especificar';
            $data[] = $unspecified;
        }

        return ['labels' => $labels, 'data' => $data];
    }
}