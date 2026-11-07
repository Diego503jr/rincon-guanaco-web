<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccommodationController extends Controller
{
    public function index()
{
    $accommodations = Accommodation::with('destination')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($accommodation) {
            if ($accommodation->image_url && !str_starts_with($accommodation->image_url, 'http')) {
                $accommodation->image_url = asset('storage/' . $accommodation->image_url);
            }
            return $accommodation;
        });

    return response()->json($accommodations);
}
    public function show($id)
    {
        $accommodation = Accommodation::with('destination')->findOrFail($id);
        return response()->json([
            'accommodation' => $accommodation
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'name' => 'required|string|max:150',
            'type' => 'required|in:hotel,apartamento,hostal',
            'address' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|image|max:5120',
            'contact_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'status' => 'nullable|in:activo,inactivo',
            'check_in' => 'required',
            'check_out' => 'required',
            'reserva' => 'nullable|integer',
        ]);

        $validated['status'] = $validated['status'] ?? 'activo';

        if ($request->hasFile('image_url')) {
            $path = $request->file('image_url')->store('accommodations', 'public');
            $validated['image_url'] = $path;
        }

        $accommodation = Accommodation::create($validated);
        $accommodation->load('destination');

        return response()->json([
            'message' => 'Alojamiento creado exitosamente.',
            'accommodation' => $accommodation
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);

        $validated = $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'name' => 'required|string|max:150',
            'type' => 'required|in:hotel,apartamento,hostal',
            'address' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|image|max:5120',
            'contact_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'status' => 'required|in:activo,inactivo',
            'check_in' => 'required',
            'check_out' => 'required',
            'reserva' => 'nullable|integer',
        ]);

        if ($request->hasFile('image_url')) {
            if ($accommodation->image_url) {
                Storage::disk('public')->delete($accommodation->image_url);
            }
            $validated['image_url'] = $request->file('image_url')->store('accommodations', 'public');
        }

        $accommodation->update($validated);
        $accommodation->load('destination');

        return response()->json([
            'message' => 'Alojamiento actualizado correctamente.',
            'accommodation' => $accommodation
        ]);
    }

    public function destroy(int $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $accommodation->status = 'inactivo';
        $accommodation->save();

        return response()->json(['message' => 'Alojamiento desactivado correctamente.']);
    }

    // Obtener alojamientos filtrados por ID de destino
    public function getByDestination(int $destinationId)
    {
        $accommodations = Accommodation::where('destination_id', $destinationId)
            ->where('status', 'activo')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($accommodation) {
                if ($accommodation->image_url && !str_starts_with($accommodation->image_url, 'http')) {
                    $accommodation->image_url = asset('storage/' . $accommodation->image_url);
                }
                return $accommodation;
            });

        return response()->json($accommodations);
    }
}
