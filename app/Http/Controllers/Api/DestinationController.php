<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DestinationController extends Controller
{
    // Listar todos los destinos
    public function index()
    {
        $destinations = Destination::orderBy('id', 'asc')->get();
        return response()->json($destinations);
    }

    // Mostrar un destino individual para edición
    public function show(int $id)
    {
        $destination = Destination::findOrFail($id);
        return response()->json($destination);
    }

    // Guardar un nuevo destino
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|in:playa,montaña,cultural,gastronomico,aventura,relax',
            'traveler_type' => 'nullable|in:mochilero,familia,pareja',
            'department' => 'required|in:Sonsonate,San Salvador',
            'municipality' => 'required|in:Izalco,San Jacinto',
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
    public function update(Request $request,int $id)
    {
        $destination = Destination::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|in:playa,montaña,cultural,gastronomico,aventura,relax',
            'traveler_type' => 'nullable|in:mochilero,familia,pareja',
            'department' => 'required|in:Sonsonate,San Salvador',
            'municipality' => 'required|in:Izalco,San Jacinto',
            'description' => 'nullable|string',
            'image_url' => 'nullable|image|max:5120',
            'latitude' => 'required|numeric',   // <-- mapa de edición
            'longitude' => 'required|numeric', // <-- mapa de edición
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
}
