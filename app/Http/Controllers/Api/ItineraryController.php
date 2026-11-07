<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use Illuminate\Http\Request;

// API de itinerarios para la app móvil: cada usuario solo ve y modifica los suyos
class ItineraryController extends Controller
{
    // Mis itinerarios (pantalla "Mis Itinerarios y Viajes")
    public function index(Request $request)
    {
        $itineraries = Itinerary::where('user_id', $request->user()->id)
            ->with(['items' => fn($query) => $query->orderBy('date_time')
                ->with('activity.destination:id,name,municipality,department,image_url')])
            ->with(['accommodationItems' => fn($query) => $query->orderBy('check_in')
                ->with('accommodation.destination:id,name,municipality,department,image_url')])
            ->orderBy('is_completed')            // primero los que están en curso
            ->orderByDesc('created_at')
            ->get();

        // Datos listos para la tarjeta: foto, ubicación y fechas del viaje
        $result = $itineraries->map(function ($itinerary) {
            $items = $itinerary->items;
            $stays = $itinerary->accommodationItems;

            $destinations = $items->pluck('activity.destination')
                ->merge($stays->pluck('accommodation.destination'))
                ->filter()->unique('id');
            $firstDestination = $destinations->first();

            // El viaje empieza en la primera actividad o entrada, y termina en la última actividad o salida
            $dates = $items->pluck('date_time')
                ->merge($stays->pluck('check_in'))
                ->merge($stays->pluck('check_out'))
                ->filter()->sort()->values();

            return [
                'id'                   => $itinerary->id,
                'title'                => $itinerary->title,
                'is_completed'         => $itinerary->is_completed,
                'total_price'          => $itinerary->total_price,
                'activities_count'     => $items->count(),
                'accommodations_count' => $stays->count(),
                'start_date'           => $dates->first()?->format('Y-m-d H:i:s'),
                'end_date'             => $dates->last()?->format('Y-m-d H:i:s'),
                'location'             => $destinations->pluck('name')->implode(', ') ?: null,
                'cover_image'          => $this->imageUrl($firstDestination?->image_url),
                'created_at'           => $itinerary->created_at,
            ];
        });

        return response()->json($result);
    }

    // Crear un viaje nuevo (vacío)
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
        ]);

        $itinerary = Itinerary::create([
            'user_id'      => $request->user()->id,
            'title'        => $data['title'],
            'is_completed' => false,
            'total_price'  => 0,
        ]);

        return response()->json([
            'message'   => 'Itinerario creado correctamente.',
            'itinerary' => $itinerary,
        ], 201);
    }

    // Detalle de un itinerario con sus actividades agrupadas por día ("Día 1", "Día 2"...)
    public function show(Request $request, int $id)
    {
        $itinerary = $this->findOwned($request, $id);

        $items = $itinerary->items()
            ->with('activity.destination:id,name,municipality,department,image_url')
            ->orderBy('date_time')
            ->get();

        $firstDay = $items->first()?->date_time->copy()->startOfDay();

        $days = $items
            ->groupBy(fn($item) => $item->date_time->format('Y-m-d'))
            ->values()
            ->map(function ($dayItems) use ($firstDay) {
                $date = $dayItems->first()->date_time->copy()->startOfDay();
                $number = (int) $firstDay->diffInDays($date) + 1;

                return [
                    'day'        => $number,
                    'label'      => "Día {$number}",
                    'date'       => $date->format('Y-m-d'),
                    'activities' => $dayItems->map(fn($item) => [
                        'id'          => $item->id,            // id del registro en el itinerario (para editar/quitar)
                        'activity_id' => $item->activity_id,
                        'title'       => $item->title,
                        'people'      => $item->people,
                        'subtotal'    => $item->subtotal,                // precio × personas
                        'date_time'   => $item->date_time->format('Y-m-d H:i:s'),
                        'time'        => $item->date_time->format('H:i'),
                        'location'    => $this->location($item->activity?->destination),
                        'image'       => $this->imageUrl($item->activity?->destination?->image_url),
                        'activity'    => $item->activity,
                    ])->values(),
                ];
            });

        // Alojamientos reservados (pestaña Alojamiento del detalle)
        $accommodations = $itinerary->accommodationItems()
            ->with('accommodation.destination:id,name,municipality,department,image_url')
            ->orderBy('check_in')
            ->get()
            ->map(fn($stay) => [
                'id'               => $stay->id,            // id del registro en el itinerario (para editar/quitar)
                'accommodation_id' => $stay->accommodation_id,
                'name'             => $stay->accommodation?->name,
                'type'             => $stay->accommodation?->type,
                'guests'           => $stay->guests,
                'nights'           => $stay->nights,
                'price_per_night'  => $stay->accommodation?->price,
                'total_price'      => $stay->total_price,
                'status'           => $stay->status,
                'check_in'         => $stay->check_in->format('Y-m-d H:i:s'),
                'check_out'        => $stay->check_out->format('Y-m-d H:i:s'),
                'location'         => $this->location($stay->accommodation?->destination),
                'image'            => $this->imageUrl($stay->accommodation?->image_url ?? $stay->accommodation?->destination?->image_url),
            ]);

        return response()->json([
            'id'             => $itinerary->id,
            'title'          => $itinerary->title,
            'is_completed'   => $itinerary->is_completed,
            'total_price'    => $itinerary->total_price,
            'created_at'     => $itinerary->created_at,
            'days'           => $days,
            'accommodations' => $accommodations,
        ]);
    }

    // Renombrar o marcar como completado
    public function update(Request $request, int $id)
    {
        $itinerary = $this->findOwned($request, $id);

        $data = $request->validate([
            'title'        => ['sometimes', 'required', 'string', 'max:150'],
            'is_completed' => ['sometimes', 'boolean'],
        ]);

        $itinerary->update($data);

        return response()->json([
            'message'   => 'Itinerario actualizado correctamente.',
            'itinerary' => $itinerary,
        ]);
    }

    // Eliminar un itinerario (sus actividades y alojamientos se borran en cascada)
    public function destroy(Request $request, int $id)
    {
        $this->findOwned($request, $id)->delete();

        return response()->json(['message' => 'Itinerario eliminado correctamente.']);
    }

    // Convierte la ruta guardada ("destinations/foto.jpg") en una URL completa para la app
        private function imageUrl(?string $path): ?string
    {
        if (! $path) return null;

        return str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
    }

    // "Volcán de Izalco · Izalco, Sonsonate"
    private function location($destination): ?string
    {
        if (! $destination) return null;

        return "{$destination->name} · {$destination->municipality}, {$destination->department}";
    }

    // Busca el itinerario solo entre los del usuario 
    private function findOwned(Request $request, int $id): Itinerary
    {
        return Itinerary::where('user_id', $request->user()->id)->findOrFail($id);
    }
}