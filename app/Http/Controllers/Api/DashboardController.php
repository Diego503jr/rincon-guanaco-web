<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        // ===== Tarjetas de contadores =====
        $counters = [
            'users'              => User::where('role', 'user')->count(),
            'destinations'       => DB::table('destinations')->where('status', 'activo')->count(),
            'active_itineraries' => Itinerary::where('is_completed', false)->count(),
            'reviews'            => Review::count(),
            'avg_rating'         => round((float) Review::avg('rating'), 1),
        ];

        return response()->json([
            'counters'             => $counters,
            'itineraries_by_month' => $this->itinerariesByMonth(),
            'top_destinations'     => $this->topDestinations(),
        ]);
    }

    // Itinerarios creados en los últimos 6 meses 
    private function itinerariesByMonth(): array
    {
        $start = now()->startOfMonth()->subMonths(5);

        $counts = Itinerary::where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn($itinerary) => $itinerary->created_at->format('Y-m'))
            ->map->count();

        $labels = [];
        $data = [];

        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = ucfirst($month->locale('es')->translatedFormat('M Y'));
            $data[] = $counts[$month->format('Y-m')] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    // Destinos que más aparecen en itinerarios (vía sus actividades)
    private function topDestinations(): array
    {
        $rows = DB::table('itineraries_activities')
            ->join('activities', 'activities.id', '=', 'itineraries_activities.activity_id')
            ->join('destinations', 'destinations.id', '=', 'activities.destination_id')
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
}