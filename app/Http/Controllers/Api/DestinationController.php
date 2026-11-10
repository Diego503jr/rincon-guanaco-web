<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DestinationController extends Controller
{
    // Listar todos los destinos
    public function index(Request $request)
    {
        $query = Destination::query();

        // Filtrar por categoría si viene en el request
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Usamos $query en lugar de Destination para respetar el filtro anterior
        $destinations = $query->orderBy('id', 'asc')->get()->map(function ($destination) {
            if ($destination->image_url && !str_starts_with($destination->image_url, 'http')) {
                $destination->image_url = asset('storage/' . $destination->image_url);
            }
            return $destination;
        });

        return response()->json($destinations);
    }

    // Mostrar un destino individual para edición
    public function show(int $id)
    {
        $destination = Destination::findOrFail($id);

        if ($destination->image_url && !str_starts_with($destination->image_url, 'http')) {
            $destination->image_url = asset('storage/' . $destination->image_url);
        }

        return response()->json($destination);
    }

    // Guardar un nuevo destino
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|in:playa,montaña,cultural,gastronomico,aventura,relax',
            'traveler_type' => 'nullable|in:mochilero,familia,pareja',
            'department' => 'required|string|max:100', // <-- Actualizado
            'municipality' => 'required|string|max:100', // <-- Actualizado
            'description' => 'nullable|string',
            'image_url' => 'nullable|image|max:5120',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($request->hasFile('image_url')) {
            $path = $request->file('image_url')->store('destinations', 'public');
            $validated['image_url'] = $path;
        }

        $destination = Destination::create($validated);

        return response()->json([
            'message' => 'Destino creado exitosamente.',
            'destination' => $destination
        ], 201);
    }

    // Actualizar un destino existente
    public function update(Request $request, int $id)
    {
        $destination = Destination::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|in:playa,montaña,cultural,gastronomico,aventura,relax',
            'traveler_type' => 'nullable|in:mochilero,familia,pareja',
            'department' => 'required|string|max:100', // <-- Actualizado
            'municipality' => 'required|string|max:100', // <-- Actualizado
            'description' => 'nullable|string',
            'image_url' => 'nullable|image|max:5120',
            'latitude' => 'required|numeric',   
            'longitude' => 'required|numeric', 
            'status' => 'required|in:activo,inactivo',
        ]);

        if ($request->hasFile('image_url')) {
            if ($destination->image_url) {
                Storage::disk('public')->delete($destination->image_url);
            }
            $validated['image_url'] = $request->file('image_url')->store('destinations', 'public');
        }

        $destination->update($validated);

        return response()->json([
            'message' => 'Destino actualizado correctamente.',
            'destination' => $destination
        ]);
    }

    public function destroy(int $id)
    {
        $destination = Destination::findOrFail($id);

        // Eliminado lógico: cambiamos el estado a inactivo
        $destination->status = 'inactivo';
        $destination->save();

        return response()->json(['message' => 'Destino desactivado correctamente.']);
    }

    //Obtener destinos por usuario asociados a itinerario completo 
    public function visitedByUser(Request $request)
    {
        // Validación de seguridad
        if (!$request->user()) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $userId = $request->user()->id;

        $destinationsFromActivities = Destination::whereHas('activities.itineraries', function ($query) use ($userId) {
            $query->where('itineraries.user_id', $userId)
                ->where('itineraries.is_completed', 1);
        })->get();

        $destinationsFromAccommodations = Destination::whereHas('accommodations.itineraries', function ($query) use ($userId) {
            $query->where('itineraries.user_id', $userId)
                ->where('itineraries.is_completed', 1);
        })->get();

        $visitedDestinations = $destinationsFromActivities->merge($destinationsFromAccommodations)->unique('id')->values();

        return response()->json($visitedDestinations);
    }

    // Obtener destino para sugerencias (cercanas)
    public function getNearby(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (!$latitude || !$longitude) {
            return response()->json(['message' => 'Se requieren latitud y longitud'], 422);
        }

        $destinations = Destination::select('*', DB::raw("(
        6371 * acos(
            cos(radians(?)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + 
            sin(radians(?)) * sin(radians(latitude))
        )
    ) AS distance"))
            ->setBindings([$latitude, $longitude, $latitude])
            ->orderBy('distance', 'asc')
            ->limit(10)
            ->get();

        return response()->json($destinations);
    }
}
