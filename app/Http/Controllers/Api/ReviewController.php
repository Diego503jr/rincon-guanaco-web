<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search') ?? $request->input('destination');
        $travelerType = strtolower($request->input('traveler_type', 'todos'));

        $isNearby = $request->boolean('nearby');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        $query = Review::with(['user', 'destination'])
            ->select('reviews.*')
            ->where('reviews.status', 'activa');

        // Búsqueda por texto
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reviews.comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('destination', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filtro de Viajero
        if ($travelerType && $travelerType !== 'todos') {
            $query->whereHas('user', function ($q) use ($travelerType) {
                $q->where('traveler_type', $travelerType);
            });
        }

        // Ordenamiento por distancia
        if ($isNearby && $latitude && $longitude) {
            $lat = (float) $latitude;
            $lng = (float) $longitude;

            $query->join('destinations', 'reviews.destination_id', '=', 'destinations.id')
                // Inyectamos la distancia directamente
                ->selectRaw("reviews.*, (6371 * acos(
                      cos(radians(?)) * cos(radians(destinations.latitude)) * 
                      cos(radians(destinations.longitude) - radians(?)) + 
                      sin(radians(?)) * sin(radians(destinations.latitude))
                  )) AS distance", [$lat, $lng, $lat])
                ->orderByRaw('distance ASC');
        } else {
            $query->orderBy('reviews.created_at', 'desc');
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'rating'         => 'required|integer|min:1|max:5',
            'travel_date'    => 'required|date',
            'comment'        => 'nullable|string',
        ]);

        $review = Review::create([
            'user_id'        => $request->user()->id,
            'destination_id' => $validated['destination_id'],
            'rating'         => $validated['rating'],
            'travel_date'    => $validated['travel_date'],
            'comment'        => $validated['comment'] ?? null,
            'status'         => 'activa',
        ]);

        return response()->json([
            'message' => 'Reseña creada',
            'data' => $review
        ], 201);
    }
}
