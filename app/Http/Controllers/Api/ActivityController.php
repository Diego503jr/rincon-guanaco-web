<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    // Listar todas las actividades junto con su destino asociado
    public function index()
    {
        $activities = Activity::with('destination')->orderBy('created_at', 'desc')->get();
        return response()->json($activities);
    }

    // Guardar una nueva actividad
    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'activity_name' => 'required|string|max:150',
            'notes' => 'nullable|string',
            'people' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'duracion' => 'required',
        ]);

        $activity = Activity::create($validated);

        return response()->json([
            'message' => 'Actividad creada exitosamente.',
            'activity' => $activity
        ], 201);
    }

    // Actualizar una actividad existente
    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);

        $validated = $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'activity_name' => 'required|string|max:150',
            'notes' => 'nullable|string',
            'people' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'duracion' => 'required',
        ]);

        $activity->update($validated);

        return response()->json([
            'message' => 'Actividad actualizada correctamente.',
            'activity' => $activity
        ]);
    }

    public function destroy(int $id)
    {
        $activity = Activity::findOrFail($id);

        // Eliminado lógico: cambiamos el estado a inactivo
        $activity->status = 'inactivo';
        $activity->save();

        return response()->json(['message' => 'Actividad desactivada correctamente.']);
    }
}
