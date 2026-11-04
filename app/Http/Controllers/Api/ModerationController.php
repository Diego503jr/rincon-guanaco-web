<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportReview;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    //WEB
    public function getReports()
    {
        // Trae los reportes junto con la información del usuario y la reseña original
        $reports = ReportReview::with(['reporter', 'review.user'])
            ->where('status', 'activo')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reports);
    }

    //Descartar reporte (inactivo)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'string']
        ]);

        $report = \App\Models\ReportReview::findOrFail($id);
        $report->status = $request->status;
        $report->save();

        return response()->json(['message' => 'Estado actualizado correctamente.']);
    }

    public function destroyReport($id)
    {
        // Buscamos el reporte específico y lo eliminamos físicamente
        $report = \App\Models\ReportReview::findOrFail($id);
        $report->delete();

        return response()->json(['message' => 'Reporte eliminado físicamente.']);
    }

    //MÓVIL
    public function store(Request $request)
    {
        $validated = $request->validate([
            'review_id'   => 'required|exists:reviews,id',
            'nota'        => 'required|string', 
            'type_report' => 'required|string', 
        ]);

        $report = ReportReview::create([
            'user_id'     => $request->user()->id,
            'review_id'   => $validated['review_id'],
            'nota'        => $validated['nota'],
            'type_report' => $validated['type_report'],
            'status'      => 'activo', // Se crea activo para revisión del administrador
        ]);

        return response()->json([
            'message' => 'Gracias, hemos recibido tu reporte y será revisado'
        ], 201);
    }
}
