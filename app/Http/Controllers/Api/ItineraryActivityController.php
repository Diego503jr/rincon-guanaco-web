<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Itinerary;
use App\Models\ItineraryActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Actividades dentro de un itinerario (modales Activity-Add y Activity-List de la app)
class ItineraryActivityController extends Controller
{
    // Catálogo de actividades activas para elegir en el modal (opcional: ?destination_id=)
    public function available(Request $request)
    {
        $request->validate([
            'destination_id' => ['nullable', 'integer'],
        ]);

        $activities = Activity::with('destination:id,name,image_url')
            ->where('status', 'activo')
            ->when($request->destination_id, fn($query, $id) => $query->where('destination_id', $id))
            ->orderBy('activity_name')
            ->get();

        return response()->json($activities);
    }

    // Agregar una actividad al itinerario (modal Activity-Add)
    public function store(Request $request, int $itineraryId)
    {
        $itinerary = $this->findOwned($request, $itineraryId);

        $data = $request->validate([
            'activity_id' => ['required', Rule::exists('activities', 'id')->where('status', 'activo')],
            'date_time'   => ['required', 'date'],
            'people'      => ['nullable', 'integer', 'min:1', 'max:50'],
            'title'       => ['nullable', 'string', 'max:150'],
        ], [
            'activity_id.exists' => 'La actividad no existe o está inactiva.',
        ]);

        $activity = Activity::find($data['activity_id']);

        $item = $itinerary->items()->create([
            'activity_id' => $activity->id,
            // Si el usuario no escribe un título, se usa el nombre de la actividad
            'title'       => $data['title'] ?? $activity->activity_name,
            'people'      => $data['people'] ?? 1,
            'date_time'   => $data['date_time'],
        ]);

        $itinerary->recalculateTotal();

        return response()->json([
            'message'     => 'Actividad agregada al itinerario.',
            'item'        => $item->load('activity'),
            'total_price' => $itinerary->total_price,
        ], 201);
    }

    // Cambiar título, hora (mover de día) o personas de una actividad del itinerario (modal Activity-Edit)
    public function update(Request $request, int $itineraryId, int $itemId)
    {
        $itinerary = $this->findOwned($request, $itineraryId);
        $item = $itinerary->items()->findOrFail($itemId);

        $data = $request->validate([
            'title'     => ['sometimes', 'required', 'string', 'max:150'],
            'date_time' => ['sometimes', 'required', 'date'],
            'people'    => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
        ]);

        $item->update($data);

        // Si cambiaron las personas, cambia el total del itinerario
        $itinerary->recalculateTotal();

        return response()->json([
            'message'     => 'Actividad del itinerario actualizada.',
            'item'        => $item->load('activity'),
            'total_price' => $itinerary->total_price,
        ]);
    }

    // Quitar una actividad del itinerario (no borra la actividad del catálogo)
    public function destroy(Request $request, int $itineraryId, int $itemId)
    {
        $itinerary = $this->findOwned($request, $itineraryId);
        $itinerary->items()->findOrFail($itemId)->delete();

        $itinerary->recalculateTotal();

        return response()->json([
            'message'     => 'Actividad quitada del itinerario.',
            'total_price' => $itinerary->total_price,
        ]);
    }

    // Busca el itinerario solo entre los del usuario (si es de otro, responde 404)
    private function findOwned(Request $request, int $id): Itinerary
    {
        return Itinerary::where('user_id', $request->user()->id)->findOrFail($id);
    }
}