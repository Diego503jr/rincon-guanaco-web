<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Itinerary;
use App\Models\ItineraryAccommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

// Alojamientos dentro de un itinerario (modal Reservar Alojamiento de la app)
class ItineraryAccommodationController extends Controller
{
    // Reservar un alojamiento en el itinerario
    public function store(Request $request, int $itineraryId)
    {
        $itinerary = $this->findOwned($request, $itineraryId);

        $data = $request->validate([
            'accommodation_id' => ['required', Rule::exists('accommodations', 'id')->where('status', 'activo')],
            'check_in'         => ['required', 'date'],
            'check_out'        => ['required', 'date', 'after:check_in'],
            'guests'           => ['nullable', 'integer', 'min:1', 'max:20'],
        ], [
            'accommodation_id.exists' => 'El alojamiento no existe o está inactivo.',
            'check_out.after'         => 'La fecha de salida debe ser posterior a la de entrada.',
        ]);

        $accommodation = Accommodation::find($data['accommodation_id']);

        $item = new ItineraryAccommodation([
            'accommodation_id' => $accommodation->id,
            'guests'           => $data['guests'] ?? 1,
            'status'           => 'confirmada',
        ]);
        $this->fillDates($item, $accommodation, $data['check_in'], $data['check_out']);

        $itinerary->accommodationItems()->save($item);
        $itinerary->recalculateTotal();

        return response()->json([
            'message'     => 'Alojamiento agregado al itinerario.',
            'item'        => $this->format($item->load('accommodation')),
            'total_price' => $itinerary->total_price,
        ], 201);
    }

    // Cambiar fechas o huéspedes de un alojamiento del itinerario (modal en modo editar)
    public function update(Request $request, int $itineraryId, int $itemId)
    {
        $itinerary = $this->findOwned($request, $itineraryId);
        $item = $itinerary->accommodationItems()->with('accommodation')->findOrFail($itemId);

        $data = $request->validate([
            'check_in'  => ['sometimes', 'required', 'date'],
            'check_out' => ['sometimes', 'required', 'date'],
            'guests'    => ['sometimes', 'required', 'integer', 'min:1', 'max:20'],
        ]);

        $checkIn  = $data['check_in'] ?? $item->check_in->format('Y-m-d');
        $checkOut = $data['check_out'] ?? $item->check_out->format('Y-m-d');

        if (Carbon::parse($checkOut)->startOfDay()->lte(Carbon::parse($checkIn)->startOfDay())) {
            return response()->json([
                'message' => 'La fecha de salida debe ser posterior a la de entrada.',
                'errors'  => ['check_out' => ['La fecha de salida debe ser posterior a la de entrada.']],
            ], 422);
        }

        if (isset($data['guests'])) {
            $item->guests = $data['guests'];
        }
        $this->fillDates($item, $item->accommodation, $checkIn, $checkOut);
        $item->save();

        $itinerary->recalculateTotal();

        return response()->json([
            'message'     => 'Alojamiento del itinerario actualizado.',
            'item'        => $this->format($item),
            'total_price' => $itinerary->total_price,
        ]);
    }

    // Quitar un alojamiento del itinerario (no borra el alojamiento del catálogo)
    public function destroy(Request $request, int $itineraryId, int $itemId)
    {
        $itinerary = $this->findOwned($request, $itineraryId);
        $itinerary->accommodationItems()->findOrFail($itemId)->delete();

        $itinerary->recalculateTotal();

        return response()->json([
            'message'     => 'Alojamiento quitado del itinerario.',
            'total_price' => $itinerary->total_price,
        ]);
    }

    // Usa la hora de entrada/salida del alojamiento y calcula el total: precio por noche × noches
    private function fillDates(ItineraryAccommodation $item, Accommodation $accommodation, string $checkIn, string $checkOut): void
    {
        $in  = Carbon::parse($checkIn)->setTimeFromTimeString($accommodation->check_in ?? '15:00');
        $out = Carbon::parse($checkOut)->setTimeFromTimeString($accommodation->check_out ?? '11:00');

        $item->check_in  = $in;
        $item->check_out = $out;
        $item->total_price = round((float) $accommodation->price * $item->nights, 2);
    }

    // Datos listos para la app
    private function format(ItineraryAccommodation $item): array
    {
        return [
            'id'               => $item->id,
            'accommodation_id' => $item->accommodation_id,
            'name'             => $item->accommodation?->name,
            'type'             => $item->accommodation?->type,
            'guests'           => $item->guests,
            'nights'           => $item->nights,
            'price_per_night'  => $item->accommodation?->price,
            'total_price'      => $item->total_price,
            'status'           => $item->status,
            'check_in'         => $item->check_in->format('Y-m-d H:i:s'),
            'check_out'        => $item->check_out->format('Y-m-d H:i:s'),
        ];
    }

    // Busca el itinerario solo entre los del usuario (si es de otro, responde 404)
    private function findOwned(Request $request, int $id): Itinerary
    {
        return Itinerary::where('user_id', $request->user()->id)->findOrFail($id);
    }
}